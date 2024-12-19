<?php

// Include the library file
include './includes/library.php';

try {
    // Connect to the database using a function from the included library.
    $pdo = connectdb();

    // Open the CSV file for reading.
    $file = fopen('../assignment2Files/api-data.csv', 'r');
    // echo realpath('../../../../../assignment2Files/api-data.csv');

    // Skip the header row of the CSV file.
    fgetcsv($file);

    // Prepare the SQL statement for inserting data into the 'assn2_movies' table.
    $stmt = $pdo->prepare("INSERT INTO assn2_movies (genre, overview, production_company, release_date, runtime, tagline, title, vote_average, vote_count, cover) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    // Loop through each row in the CSV file.
    while (($row = fgetcsv($file)) !== false) {
        // Execute the prepared statement with values from the current row of the CSV.
        $stmt->execute([
            json_encode(json_decode($row[2])), // Convert the 'genre' column to valid JSON format.
            $row[5], // 'overview' column as plain text.
            json_encode(json_decode($row[6])), // Convert the 'production_company' column to valid JSON format.
            $row[7], // 'release_date' column in the format 'YYYY-MM-DD'.
            $row[9], // 'runtime' column as an integer (e.g., movie duration in minutes).
            $row[10], // 'tagline' column as plain text.
            $row[11], // 'title' column as plain text.
            (float)$row[12], // 'vote_average' column as a float (e.g., average rating).
            (int)$row[13], // 'vote_count' column as an integer (e.g., number of votes).
            $row[14] // 'cover' column as plain text (e.g., URL of the movie cover).
        ]);
    }

    // Close the file after reading all the data.
    fclose($file);

    // Print a success message after the data is uploaded.
    echo "Data upload success";
} catch (PDOException $e) {
    // Catch and display any PDO (database) related errors.
    echo "Error: " . $e->getMessage();
}
