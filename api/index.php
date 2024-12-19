<?php

// At the top of your API file (e.g., api/index.php)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-API-KEY");


if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$uri = $_SERVER['REQUEST_URI'];
$uri = parse_url($_SERVER['REQUEST_URI']);

define('__BASE__', '/~hhussainzada/3430/assn/Assignment2/assn2-Hikmatullah6/api');
$endpoint = str_replace(__BASE__, "", $uri["path"]);
$method = $_SERVER['REQUEST_METHOD'];

// Retrieve API key from header or query parameters
$apiKey = $_SERVER['HTTP_X_API_KEY'] ?? $_GET['api_key'] ?? '';


// Include the library for database connection
include '../includes/library.php';
$pdo = connectdb();

// Function to generate a JSON response with a given HTTP status code
function json_response($statusCode, $data)
{
    http_response_code($statusCode);
    header('Content-Type: application/json');
    $jsonData = json_encode($data);
    header('Content-Length: ' . strlen($jsonData));
    echo $jsonData;
    exit;
}

// Function to retrieve a movie by its ID
function getMoviesWithID($pdo, $id)
{
    $stmt = $pdo->prepare("SELECT * FROM assn2_movies WHERE movie_id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

// Function to retrieve a user by their API key
function getUserWithAPIKEY($pdo, $apiKey)
{
    $stmt = $pdo->prepare("SELECT * FROM assn2_users WHERE api_key = ?");
    $stmt->execute([$apiKey]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

// Function to retrieve a user by their ID
function getUserWithID($pdo, $id)
{
    $stmt = $pdo->prepare("SELECT * FROM assn2_users WHERE user_id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

// Helper function to extract an ID from the endpoint
function getIdFromEndpoint($endpoint, $position)
{
    $parts = explode("/", trim($endpoint, "/"));
    return (isset($parts[$position]) && is_numeric($parts[$position])) ? (int)$parts[$position] : null;
}

// Function to fetch the user's to-watch list along with basic movie details
function getToWatchList($pdo, $apiKey)
{
    $user = getUserWithAPIKEY($pdo, $apiKey);

    if ($user) {
        $userID = $user['user_id'];
        $stmt = $pdo->prepare("
            SELECT towatch_id, t.movie_id, m.title, m.release_date, m.vote_average, m.cover, t.priority, t.notes
            FROM assn2_toWatchList t
            JOIN assn2_movies m ON t.movie_id = m.movie_id
            WHERE t.user_id = ?
        ");
        $stmt->execute([$userID]);
        $watchList = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($watchList) {
            json_response(200, $watchList);
        } else {
            json_response(404, ["error" => "No entries found in the to-watch list"]);
        }
    } else {
        json_response(401, ["error" => "Invalid API key"]);
    }
}

function getCompletedWatchList($pdo, $apiKey)
{
    // Retrieve user based on API key
    $user = getUserWithAPIKEY($pdo, $apiKey);

    if ($user) {
        $userID = $user['user_id'];

        // Query to retrieve completed watch list entries with basic movie details
        $stmt = $pdo->prepare("
            SELECT completed_id, c.movie_id, m.title, m.release_date, m.vote_average, m.cover, c.rating, c.notes, 
                   c.date_initially_watched, c.date_last_watched, c.times_watched
            FROM assn2_completedWatchList c
            JOIN assn2_movies m ON c.movie_id = m.movie_id
            WHERE c.user_id = ?
        ");
        $stmt->execute([$userID]);
        $watchList = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Return the completed watch list or an error if no entries are found
        if ($watchList) {
            json_response(200, $watchList);
        } else {
            json_response(404, ["error" => "No entries found in the completed watch list"]);
        }
    } else {
        json_response(401, ["error" => "Invalid API key"]);
    }
}

function getUserStats($pdo, $userId)
{
    // Check if the user exists in the database
    $stmt = $pdo->prepare("SELECT user_id FROM assn2_users WHERE user_id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Total time watched by the user (sum of movie runtimes)
        $stmt = $pdo->prepare("
            SELECT SUM(m.runtime) AS total_time_watched
            FROM assn2_completedWatchList c
            JOIN assn2_movies m ON c.movie_id = m.movie_id
            WHERE c.user_id = ?
        ");
        $stmt->execute([$userId]);
        $totalTimeWatched = $stmt->fetch(PDO::FETCH_ASSOC)['total_time_watched'] ?? 0;

        // Average rating given by the user across completed movies
        $stmt = $pdo->prepare("
            SELECT AVG(c.rating) AS average_user_rating
            FROM assn2_completedWatchList c
            WHERE c.user_id = ?
        ");
        $stmt->execute([$userId]);
        $averageUserRating = $stmt->fetch(PDO::FETCH_ASSOC)['average_user_rating'] ?? 0;

        // Total number of movies watched by the user
        $stmt = $pdo->prepare("
            SELECT COUNT(*) AS total_movies_watched
            FROM assn2_completedWatchList
            WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
        $totalMoviesWatched = $stmt->fetch(PDO::FETCH_ASSOC)['total_movies_watched'] ?? 0;

        // Planned time to watch (sum of runtimes for movies in the to-watch list)
        $stmt = $pdo->prepare("
            SELECT SUM(m.runtime) AS planned_time_to_watch
            FROM assn2_toWatchList t
            JOIN assn2_movies m ON t.movie_id = m.movie_id
            WHERE t.user_id = ?
        ");
        $stmt->execute([$userId]);
        $plannedTimeToWatch = $stmt->fetch(PDO::FETCH_ASSOC)['planned_time_to_watch'] ?? 0;

        // Prepare response with user stats
        $stats = [
            'total_time_watched' => $totalTimeWatched,
            'average_user_rating' => $averageUserRating,
            'total_movies_watched' => $totalMoviesWatched,
            'planned_time_to_watch' => $plannedTimeToWatch
        ];

        json_response(200, $stats);
    } else {
        json_response(404, ["error" => "User not found"]);
    }
}

function addToWatchList($pdo, $userID, $data)
{
    // Extract and validate movie data from input
    $movieID = (int)$data['movie_id'];
    $notes = $data['notes'] ?? '';
    $priority = (int)$data['priority'];

    // Ensure priority is within valid range
    if ($priority < 1 || $priority > 10) {
        json_response(400, ["error" => "Priority must be between 1 and 10"]);
    }

    // Insert movie into to-watch list with provided details
    $stmt = $pdo->prepare("INSERT INTO assn2_toWatchList (user_id, movie_id, priority, notes) 
                            VALUES (?, ?, ?, ?)");
    $result = $stmt->execute([$userID, $movieID, $priority, $notes]);

    // Return success or failure response
    if ($result) {
        json_response(201, ["message" => "Movie added to watch list"]);
    } else {
        json_response(500, ["error" => "Failed to add movie to watch list"]);
    }
}

function addCompletedWatchList($pdo, $userId, $data)
{
    // Extract and validate rating for the completed movie
    $rating = (int)$data['rating'];
    if ($rating < 1 || $rating > 10) {
        json_response(400, ["error" => "Rating must be between 1 and 10"]);
        return;
    }

    // Retrieve additional movie data or use defaults
    $movieId = (int)$data['movie_id'];
    $notes = $data['notes'] ?? '';
    $dateInitiallyWatched = $data['date_initially_watched'] ?? date('Y-m-d');
    $dateLastWatched = $data['date_last_watched'] ?? date('Y-m-d');
    $timesWatched = (int)($data['times_watched'] ?? 1);

    // Insert completed movie entry with all details
    $stmt = $pdo->prepare("INSERT INTO assn2_completedWatchList (user_id, movie_id, rating, notes, date_initially_watched, date_last_watched, times_watched) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $result = $stmt->execute([$userId, $movieId, $rating, $notes, $dateInitiallyWatched, $dateLastWatched, $timesWatched]);

    if (!$result) {
        json_response(500, ["error" => "Failed to add movie to completed watch list"]);
        return;
    }

    // Update overall movie rating in movies table
    updateMovieRating($pdo, $movieId);

    json_response(201, ["message" => "Movie added to completed watch list"]);
}

function updateMovieRating($pdo, $movieId)
{
    // Calculate new average rating and rating count for a movie
    $stmt = $pdo->prepare("
        SELECT AVG(rating) AS new_avg_rating, COUNT(rating) AS rating_count
        FROM assn2_completedWatchList
        WHERE movie_id = ?
    ");
    $stmt->execute([$movieId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        $newAvgRating = (float)$result['new_avg_rating'];
        $ratingCount = (int)$result['rating_count'];

        // Update movie's average rating and vote count in the movies table
        $stmt = $pdo->prepare("UPDATE assn2_movies SET vote_average = ?, vote_count = ? WHERE movie_id = ?");
        $stmt->execute([$newAvgRating, $ratingCount, $movieId]);
    }
}

function authenticateUser($pdo, $data)
{
    // Extract and validate username and password
    $username = $data["username"] ?? '';
    $password = $data["password"] ?? '';

    if (empty($username) || empty($password)) {
        json_response(400, ["error" => "Username and password required"]);
        return;
    }

    // Check if user exists and password is correct
    $stmt = $pdo->prepare("SELECT * FROM assn2_users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        json_response(200, ["api" => $user['api_key']]);
    } else {
        json_response(401, ["error" => "Invalid username or password"]);
    }
}


function upsertToWatchListEntry($pdo, $userId, $entryId, $data)
{
    // Extract and validate movie data for the to-watch list
    $movieId = (int)$data['movie_id'];
    $priority = (int)$data['priority'];
    $notes = $data['notes'] ?? '';

    // Check if priority is within the valid range
    if ($priority < 1 || $priority > 10) {
        json_response(400, ["error" => "Priority must be between 1 and 10"]);
        return;
    }

    // Check if the entry already exists
    $stmt = $pdo->prepare("SELECT * FROM assn2_toWatchList WHERE towatch_id = ? AND user_id = ?");
    $stmt->execute([$entryId, $userId]);
    $existingEntry = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingEntry) {
        // Update existing entry
        $stmt = $pdo->prepare("UPDATE assn2_toWatchList SET movie_id = ?, priority = ?, notes = ? WHERE towatch_id = ? AND user_id = ?");
        $result = $stmt->execute([$movieId, $priority, $notes, $entryId, $userId]);

        if ($result) {
            json_response(200, ["message" => "To-watch list entry updated"]);
        } else {
            json_response(500, ["error" => "Failed to update to-watch list entry"]);
        }
    } else {
        // Insert new entry
        $stmt = $pdo->prepare("INSERT INTO assn2_toWatchList (user_id, movie_id, priority, notes) VALUES (?, ?, ?, ?)");
        $result = $stmt->execute([$userId, $movieId, $priority, $notes]);

        if ($result) {
            json_response(201, ["message" => "To-watch list entry created"]);
        } else {
            json_response(500, ["error" => "Failed to create to-watch list entry"]);
        }
    }
}

function updateToWatchListPriority($pdo, $userId, $entryId, $data)
{
    // Update the priority for an existing to-watch list entry
    $priority = (int)$data['priority'];

    if ($priority < 1 || $priority > 10) {
        json_response(400, ["error" => "Priority must be between 1 and 10"]);
        return;
    }

    $stmt = $pdo->prepare("UPDATE assn2_toWatchList SET priority = ? WHERE towatch_id = ? AND user_id = ?");
    $result = $stmt->execute([$priority, $entryId, $userId]);

    if ($result) {
        json_response(200, ["message" => "Priority updated successfully"]);
    } else {
        json_response(500, ["error" => "Failed to update priority"]);
    }
}

function updateCompletedWatchListRating($pdo, $userId, $entryId, $data)
{
    // Update rating for a completed movie entry
    $newRating = (float)$data['rating'];

    if ($newRating < 0 || $newRating > 10) {
        json_response(400, ["error" => "Rating must be between 0 and 10"]);
        return;
    }

    // Update the rating in the user's completed watch list
    $stmt = $pdo->prepare("UPDATE assn2_completedWatchList SET rating = ? WHERE completed_id = ? AND user_id = ?");
    $result = $stmt->execute([$newRating, $entryId, $userId]);

    if (!$result) {
        json_response(500, ["error" => "Failed to update rating"]);
        return;
    }

    // Recalculate and update the average rating in the movies table
    $stmt = $pdo->prepare("SELECT movie_id FROM assn2_completedWatchList WHERE completed_id = ?");
    $stmt->execute([$entryId]);
    $entry = $stmt->fetch(PDO::FETCH_ASSOC);
    $movieId = $entry['movie_id'];

    $stmt = $pdo->prepare("SELECT AVG(rating) as avg_rating FROM assn2_completedWatchList WHERE movie_id = ?");
    $stmt->execute([$movieId]);
    $avgRating = $stmt->fetch(PDO::FETCH_ASSOC)['avg_rating'];

    // Update movie's average rating in the movies table
    $stmt = $pdo->prepare("UPDATE assn2_movies SET vote_average = ? WHERE movie_id = ?");
    $stmt->execute([$avgRating, $movieId]);

    json_response(200, ["message" => "Rating updated successfully and movie average rating recalculated"]);
}


function incrementTimesWatched($pdo, $userId, $entryId)
{
    // Retrieve the current times watched for the specific completed watchlist entry
    $stmt = $pdo->prepare("SELECT times_watched FROM assn2_completedWatchList WHERE completed_id = ? AND user_id = ?");
    $stmt->execute([$entryId, $userId]);
    $entry = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$entry) {
        json_response(404, ["error" => "Movie entry not found"]);
        return;
    }

    // Increment the times_watched value and update the last_watched date to the current date
    $timesWatched = $entry['times_watched'] + 1;
    $lastWatchedDate = date('Y-m-d'); // Current date in YYYY-MM-DD format

    $stmt = $pdo->prepare("UPDATE assn2_completedWatchList SET times_watched = ?, date_last_watched = ? WHERE completed_id = ? AND user_id = ?");
    $result = $stmt->execute([$timesWatched, $lastWatchedDate, $entryId, $userId]);

    if ($result) {
        json_response(200, ["message" => "Times watched incremented successfully", "times_watched" => $timesWatched, "last_watched" => $lastWatchedDate]);
    } else {
        json_response(500, ["error" => "Failed to update times watched"]);
    }
}

function deleteFromWatchList($pdo, $userId, $entryId)
{
    // Check if the movie exists in the user's to-watch list
    $stmt = $pdo->prepare("SELECT * FROM assn2_toWatchList WHERE towatch_id = ? AND user_id = ?");
    $stmt->execute([$entryId, $userId]);
    $entry = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$entry) {
        json_response(404, ["error" => "Movie entry not found in watch list"]);
        return;
    }

    // Delete the movie entry from the to-watch list
    $stmt = $pdo->prepare("DELETE FROM assn2_toWatchList WHERE towatch_id = ? AND user_id = ?");
    $result = $stmt->execute([$entryId, $userId]);

    if ($result) {
        json_response(200, ["message" => "Movie successfully removed from watch list"]);
    } else {
        json_response(500, ["error" => "Failed to delete movie from watch list"]);
    }
}

function deleteFromCompletedWatchList($pdo, $userId, $entryId)
{
    // Check if the movie exists in the user's completed watch list
    $stmt = $pdo->prepare("SELECT * FROM assn2_completedWatchList WHERE completed_id = ? AND user_id = ?");
    $stmt->execute([$entryId, $userId]);
    $entry = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$entry) {
        json_response(404, ["error" => "Movie entry not found in completed watch list"]);
        return;
    }

    // Delete the movie entry from the completed watch list
    $stmt = $pdo->prepare("DELETE FROM assn2_completedWatchList WHERE completed_id = ? AND user_id = ?");
    $result = $stmt->execute([$entryId, $userId]);

    if ($result) {
        json_response(200, ["message" => "Movie successfully removed from completed watch list"]);
    } else {
        json_response(500, ["error" => "Failed to delete movie from completed watch list"]);
    }
}

// Get the endpoint and request method

// Handle GET requests
if ($method == 'GET') {
    if ($endpoint == '/movies') {
        // Retrieve and return a list of up to 100 movies with basic information.
        // $stmt = $pdo->query("SELECT movie_id, cover, title, vote_average FROM assn2_movies LIMIT 100");
        $stmt = $pdo->query("SELECT * FROM assn2_movies");
        $result = $stmt->fetchAll();
        json_response(200, $result);
    } elseif (preg_match('/^\/movies\/(\d+)$/', $endpoint, $matches)) {
        // Retrieve specific movie details by ID.
        $id = (int)$matches[1];
        $movie = getMoviesWithID($pdo, $id);
        $movie ? json_response(200, $movie) : json_response(404, ["error" => "Movie not found"]);
    } elseif (preg_match('/^\/movies\/(\d+)\/rating$/', $endpoint, $matches)) {
        // Retrieve the rating of a specific movie by ID.
        $id = (int)$matches[1];
        $movie = getMoviesWithID($pdo, $id);
        if ($movie) {
            isset($movie['vote_average']) ? json_response(200, $movie['vote_average']) : json_response(404, ["error" => "Rating not found for the specified movie"]);
        } else {
            json_response(404, ["error" => "Movie not found"]);
        }
    } elseif ($endpoint == '/towatchlist/entries') {
        // Get the user's to-watch list based on their API key.
        getToWatchList($pdo, $apiKey);
    } elseif ($endpoint == '/completedwatchlist/entries') {
        // Get the user's completed watch list based on their API key.
        getCompletedWatchList($pdo, $apiKey);
    } elseif (preg_match('/^\/completedwatchlist\/entries\/(\d+)\/times-watched$/', $endpoint, $matches)) {
        // Retrieve how many times a specific movie was watched by the user.
        $id = (int)$matches[1];
        $user = getUserWithAPIKEY($pdo, $apiKey);
        $movie = getMoviesWithID($pdo, $id);
        if ($user) {
            $userID = $user['user_id'];
            $movieID = $movie['movie_id'];
            $stmt = $pdo->prepare("SELECT times_watched FROM assn2_completedWatchList WHERE user_id = ? AND movie_id = ?");
            $stmt->execute([$userID, $movieID]);
            $timesWatched = $stmt->fetch(PDO::FETCH_ASSOC);
            $timesWatched ? json_response(200, $timesWatched) : json_response(404, ["error" => "Movie not found in the completed watch list"]);
        } else {
            json_response(401, ["error" => "Invalid API key"]);
        }
    } elseif (preg_match('/^\/completedwatchlist\/entries\/(\d+)\/rating$/', $endpoint, $matches)) {
        // Retrieve the user's rating for a specific movie in the completed watch list.
        $id = (int)$matches[1];
        $user = getUserWithAPIKEY($pdo, $apiKey);
        $movie = getMoviesWithID($pdo, $id);
        if ($user) {
            $userID = $user['user_id'];
            $movieID = $movie['movie_id'];
            $stmt = $pdo->prepare("SELECT rating FROM assn2_completedWatchList WHERE user_id = ? AND movie_id = ?");
            $stmt->execute([$userID, $movieID]);
            $rating = $stmt->fetch(PDO::FETCH_ASSOC);
            $rating ? json_response(200, $rating) : json_response(404, ["error" => "Movie not found in the completed watch list"]);
        } else {
            json_response(401, ["error" => "Invalid API key"]);
        }
    } elseif (preg_match('/^\/users\/(\d+)\/stats$/', $endpoint, $matches)) {
        // Retrieve user stats based on their user ID.
        $id = (int)$matches[1];
        getUserStats($pdo, $id);
    } else {
        // Return a 404 error if the endpoint does not match any case.
        json_response(404, ["error" => "Endpoint not found"]);
    }
}

// Handle POST requests
else if ($method == 'POST') {
    if ($endpoint == '/towatchlist/entries') {
        // Add a new entry to the user's to-watch list.
        $user = getUserWithAPIKEY($pdo, $apiKey);
        if ($user) {
            $userID = $user["user_id"];
            $data = json_decode(file_get_contents("php://input"), true);
            // Validate necessary data fields.
            if (!isset($data['movie_id'], $data['priority']) || !is_numeric($data['movie_id']) || !is_numeric($data['priority'])) {
                json_response(400, ["error" => "Invalid input data"]);
            } else {
                addToWatchList($pdo, $userID, $data);
            }
        } else {
            json_response(401, ["error" => "Invalid API key"]);
        }
    } elseif ($endpoint == '/completedwatchlist/entries') {
        // Add a new entry to the user's completed watch list.
        $user = getUserWithAPIKEY($pdo, $apiKey);
        if ($user) {
            $data = json_decode(file_get_contents("php://input"), true);
            $userID = $user["user_id"];
            // Validate necessary data fields.
            if (!isset($data['movie_id'], $data['rating']) || !is_numeric($data['movie_id']) || !is_numeric($data['rating'])) {
                json_response(400, ["error" => "Invalid input data"]);
            } else {
                addCompletedWatchList($pdo, $userID, $data);
            }
        } else {
            json_response(401, ["error" => "Invalid API key"]);
        }
    } elseif ($endpoint == '/users/session') {
        // Authenticate user and return API key.
        $data = json_decode(file_get_contents("php://input"), true);
        authenticateUser($pdo, $data);
    } else {
        // Return a 404 error if the endpoint does not match any case.
        json_response(404, ["error" => "Endpoint not found"]);
    }
}


// Handle PUT requests
else if ($method == 'PUT' && preg_match('/^\/towatchlist\/entries\/(\d+)$/', $endpoint, $matches)) {
    // Extract the entry ID directly from the URI
    $entryID = (int)$matches[1];

    // Authenticate user based on the API key
    $user = getUserWithAPIKEY($pdo, $apiKey);
    if ($user) {
        $userID = $user["user_id"];
        $data = json_decode(file_get_contents("php://input"), true);

        // Validate required data fields
        if (!isset($data['movie_id'], $data['priority']) || !is_numeric($data['movie_id']) || !is_numeric($data['priority'])) {
            json_response(400, ["error" => "Invalid input data"]);
        } else {
            upsertToWatchListEntry($pdo, $userID, $entryID, $data);
        }
    } else {
        json_response(401, ["error" => "Invalid API Key"]);
    }
}


// Handle PATCH requests
else if ($method == 'PATCH') {
    if (preg_match('/^\/towatchlist\/entries\/(\d+)\/priority$/', $endpoint, $matches)) {
        // Update priority of a to-watch list entry.
        $entryID = (int)$matches[1];
        $user = getUserWithAPIKEY($pdo, $apiKey);
        if ($user) {
            $userID = $user["user_id"];
            $data = json_decode(file_get_contents("php://input"), true);
            // Validate priority field.
            if (!isset($data['priority']) || !is_numeric($data['priority'])) {
                json_response(400, ["error" => "Invalid priority data"]);
            } else {
                updateToWatchListPriority($pdo, $userID, $entryID, $data);
            }
        } else {
            json_response(401, ["error" => "Invalid API Key"]);
        }
    } else if (preg_match('/^\/completedwatchlist\/entries\/(\d+)\/rating$/', $endpoint, $matches)) {
        // Update rating for an entry in the completed watch list.
        $entryID = (int)$matches[1];
        $user = getUserWithAPIKEY($pdo, $apiKey);
        if ($user) {
            $userID = $user["user_id"];
            $data = json_decode(file_get_contents("php://input"), true);
            // Validate rating field.
            if (!isset($data['rating']) || !is_numeric($data['rating'])) {
                json_response(400, ["error" => "Invalid rating data"]);
                return;
            } else {
                updateCompletedWatchListRating($pdo, $userID, $entryID, $data);
            }
        } else {
            json_response(401, ["error" => "Invalid API Key"]);
        }
    } else if (preg_match('/^\/completedwatchlist\/entries\/(\d+)\/times-watched$/', $endpoint, $matches)) {
        // Increment times-watched count for a completed watch list entry.
        $entryID = (int)$matches[1];
        $user = getUserWithAPIKEY($pdo, $apiKey);
        if ($user) {
            $userID = $user["user_id"];
            incrementTimesWatched($pdo, $userID, $entryID);
        } else {
            json_response(401, ["error" => "Invalid API Key"]);
        }
    } else {
        // Return a 404 error if the endpoint does not match any case.
        json_response(404, ["error" => "Endpoint not found"]);
    }
}


// Handle DELETE requests
else if ($method == 'DELETE') {
    $user = getUserWithAPIKEY($pdo, $apiKey);

    if ($user) {
        $userID = $user["user_id"];

        if (preg_match('#^/towatchlist/entries/(\d+)$#', $endpoint, $matches)) {
            // Remove an entry from the user's to-watch list.
            $entryID = (int)$matches[1];
            deleteFromWatchList($pdo, $userID, $entryID);
        } else if (preg_match('#^/completedwatchlist/entries/(\d+)$#', $endpoint, $matches)) {
            // Remove an entry from the user's completed watch list.
            $entryID = (int)$matches[1];
            deleteFromCompletedWatchList($pdo, $userID, $entryID);
        } else {
            // Return a 404 error if the endpoint does not match any case.
            json_response(404, ["error" => "Endpoint not found"]);
        }
    } else {
        json_response(401, ["error" => "Invalid API Key"]);
    }
} else {
    // Return a 405 error if the HTTP method is not supported or the endpoint does not match any case.
    json_response(405, ["error" => "Method Not Allowed or Endpoint Not Found"]);
}
