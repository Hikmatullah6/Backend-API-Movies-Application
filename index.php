<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Documentation</title>
    <link rel="stylesheet" href="./styles/index.css">
</head>

<body>
    <header>
        <h1>Movie Watchlist API Documentation</h1>
        <p>Welcome to the Movie Watchlist API! This API allows you to manage user accounts, movies, to-watch lists, and completed watch lists. Below, you will find details on the available endpoints, parameters, and example requests.</p>
    </header>

    <section>
        <h2>Authentication</h2>
        <p>To access certain endpoints, you must include your API key in the request headers or as a query parameter.</p>
    </section>

    <section>
        <h2>Endpoints</h2>

        <h3>Account Management</h3>
        <ul>
            <li><strong>POST /create-account</strong> - Creates a new user account. Requires `username`, `email`, and `password` in the body.</li>
            <li><strong>POST /login</strong> - Authenticates a user and initiates a session. Requires `username` and `password` in the body.</li>
            <li><strong>GET /view-account</strong> - Displays the authenticated user's account details and API key. Requires authentication.</li>
            <li><strong>POST /view-account/regenerate-api-key</strong> - Regenerates a new API key for the authenticated user. Requires authentication.</li>
            <li><strong>GET /logout</strong> - Ends the user’s session.</li>
        </ul>

        <h3>Movies</h3>
        <ul>
            <li><strong>GET /movies</strong> - Retrieves a list of all movies with basic information (ID, title, cover, rating).</li>
            <li><strong>GET /movies/{id}</strong> - Retrieves detailed information for a specific movie.</li>
            <li><strong>GET /movies/{id}/rating</strong> - Retrieves the current rating for a specific movie.</li>
        </ul>

        <h3>To-Watch List</h3>
        <ul>
            <li><strong>GET /towatchlist/entries</strong> - Retrieves all entries on the user’s to-watch list. Requires authentication.</li>
            <li><strong>POST /towatchlist/entries</strong> - Adds a new movie to the user’s to-watch list. Requires `movie_id`, `priority`, and optional `notes` in the body and authentication.</li>
            <li><strong>PUT /towatchlist/entries/{id}</strong> - Updates a specific entry in the user’s to-watch list with `movie_id`, `priority`, and `notes`. Requires authentication.</li>
            <li><strong>PATCH /towatchlist/entries/{id}/priority</strong> - Updates the priority of a movie in the to-watch list. Requires `priority` in the body and authentication.</li>
            <li><strong>DELETE /towatchlist/entries/{id}</strong> - Removes a specific movie from the user’s to-watch list. Requires authentication.</li>
        </ul>

        <h3>Completed Watch List</h3>
        <ul>
            <li><strong>GET /completedwatchlist/entries</strong> - Retrieves all entries on the user’s completed watch list. Requires authentication.</li>
            <li><strong>GET /completedwatchlist/entries/{id}/times-watched</strong> - Returns the number of times the user has watched the specified movie. Requires authentication.</li>
            <li><strong>GET /completedwatchlist/entries/{id}/rating</strong> - Returns the user's rating for a specific movie. Requires authentication.</li>
            <li><strong>POST /completedwatchlist/entries</strong> - Adds a new movie to the user’s completed watch list. Requires `movie_id`, `rating`, `date_initially_watched`, `date_last_watched`, `times_watched`, and `notes` (optional) in the body and authentication.</li>
            <li><strong>PATCH /completedwatchlist/entries/{id}/rating</strong> - Updates the rating for a completed movie. Requires `rating` in the body and authentication.</li>
            <li><strong>PATCH /completedwatchlist/entries/{id}/times-watched</strong> - Increments the number of times a movie has been watched and updates the last watched date. Requires authentication.</li>
            <li><strong>DELETE /completedwatchlist/entries/{id}</strong> - Removes a specific movie from the user’s completed watch list. Requires authentication.</li>
        </ul>

        <h3>User Statistics</h3>
        <ul>
            <li><strong>GET /users/{id}/stats</strong> - Retrieves basic watching stats for a user, such as total time watched, average score, and planned time to watch. Requires authentication.</li>
        </ul>
    </section>

    <footer>
        <p>For more information, please contact support or refer to our documentation.</p>
    </footer>
</body>

</html>