[![Review Assignment Due Date](https://classroom.github.com/assets/deadline-readme-button-22041afd0340ce965d47ae6ef1cefeee28c7c493a6346c4f15d667ab976d596c.svg)](https://classroom.github.com/a/6L7vEwoF)
# COIS 3430 - Assignment #2

For this assignment you are going to build your own back-end API, along with a couple of self-processing php pages for authentication. In assignment 3 we'll use the front-end framework in conjunction with your back-end api to complete a full application.

## Partner Work

To start off, just a quick reminder that you can choose to work on this assignment with a partner. I strongly encourage partner work on this one, since although the assignment isn't more complex that what we've done it lab, it is a significant amount of work, and is the type of work that would split well with another person.

For partner work, one person should accept the github classroom invitation, and then invite the other to their git repository (you don't both need to create different repos).

Only one of you should submit the necessary links on Blackboard, letting me know the repo to mark (although in theory they should be the same) and where to access it on Loki, you must also include the name of your partner. The other person should just submit the name of their partner.

## Premise

The premise of our API is the back-end for a movie watching/reviewing site. Conceptually it will contain users, movies, a toWatch list, and a completeWatch list for each user.

Similar (although more complex) sites that include a front-end are:

- [Letterboxd](https://letterboxd.com)
- [AniList](https://anilist.co)
- [Just Watch](https://www.justwatch.com)
- [MyAnimeList](https://myanimelist.net)

Checking out one or more of these sites will give you an idea of what I mean.

Although on Assignment 2 you are only building the API back-end, Assignment 3 will include a React front-end to access your API backend. If you take the time now to think about how you want your front-end to work, you can build your API endpoints accordingly and save yourself from having to redo some of them later.

## Requirements

For this assignment there are a large number of smaller tasks you must complete.

If you try to brute force this it will probably be an overwhelming amount of work. This is an assignment where good design is going to play heavily in your favor.

As you should have noticed in Lab 5, the type of routing you did for our mini-api had a **significant** amount of repetition. One well-written function can drastically reduced that, since each time you want to send a response you can just call the function and passed it what it needed.

This assignment is going to have more error checking and many more routes. So please make good design choices to reduce repetition.

There are also similarities to other things we've done in lab (authentication) and between the different endpoints in this assignment (many of the toWatch and completedWatch endpoints are nearly identical other then the database query), so if you're smart about it, you should be able to repurpose lots of code with minor changes.

## Database Tables

Your API will require the creation of 4 database tables (at minimum). Each of the tables should have an autonumber primary key.

- The **users** table will store: username, email, password, api_key, api_date (the date the api key was added/ last changed)
- The **movies** is a little more open ended. Look at the dataset provided and decide which columns you want to use (or all of them). You need at least id, title, cover, overview and the two voting columns that make up rating, but to make your application more interesting you should consider including additional columns.
- The **toWatchList** table links a movieID and userID, plus includes a priority value between 1-10 (where 1 is high) and a notes field.
- The **completedWatchList** table links a movieID and userID, plus includes a rating, a notes field, a date initially watched, and date last watched and a number of times watched.

The data for the movies table has been provided on Blackboard. I purposely didn't put the .csv file in the repo. Please don't add it.

The columns for production companies and genre contain JSON objects. This basically means the data isn't normalized. If you're only going to display the data, leaving it in JSON form is fine, since your front-end can decode it for display. However if you want to use something like genre for a filter..you should consider splitting that data out into it's own tables (a genre table, and a movie_genre table for example). You would need to parse the csv using a one-off script in the language of your choice (you could even use php if you wanted), to split out the data. If you choose to do this, and include the script in your repo

_Each user can only have one of each list type, so you don't need to worry about a table for the lists themselves._

## Required Pages

You'll need to create the following self-processing (and sticky where relevant) php pages to allow for account creation to use your API:

- **Create Account**: The create account page should collect at minimum username, email and password (thinking of the app we're going to build in the end, you can also include other fields if you want).
  - The _username_ should be unique, the email address must be valid, and you should enforce some sort of logical and secure password strength verification.
  - Creating an account should also generate an API key for the user.
  - This information should be written to a user's table in the database (you'll need to create the table).
- **Login**: The login page should accept the username and password and verify them against the database. Then setup session to verify their authentication on the view account page.
- **View Account**: This page should show the user their account details (except password). Its purpose is largely to give them access to their API key.
  - It should also include a button/link that allows them to request a new API key, should theirs be compromised.
- **Index** The index page for the main part of your site should provide brief details about your API, as well as a list (with descriptions) of routes and endpoints. This page is basically your API documentation and should tell the users what options are available to them. This is just straight HTML.

### Other details

- Each of the pages should have a consistent design that makes them look like they are all part of the same site.
- If the user is logged in, you should display a logout button so they can logout.

_Note: You really should have a method for the user to change their password as well, but for the sake of time, we're leaving that out._

## API

- First, you'll need to complete the provided .htaccess file similar to Lab 5. I've put all the relevant parts in, you just need to change the RewriteBase.
- Then on the index page in the api folder you'll need to complete the appropriate routing for the all the required endpoints. Unlike in the lab, you should **not** complete everything in the routing file. The router should really just include other php files, and/or call appropriate functions for each route.

### Endpoints

You will need to complete appropriate routing and request completion for each endpoint below. Every endpoint must include logical validation where appropriate, and return proper success and failure HTTP codes as appropriate.

#### Movies

- **GET** & `/movies/` - should return all movies, but not all data.
  - Consider what the main display of your movies might need to include (id, cover, title, rating - maybe? ), and only return that.
  - For testing this, you might want to at least temporarily limit it to something like the first 100 rows.
- **GET** & `/movies/{id}` - returns the all columns of movie data for a specific movie.
- **GET** & `/movies/{id}/rating` - returns the rating value for a specific movie.
  - this is mostly an efficiency endpoint, so later we can get an updated rating without needing to retrieve all the data again.

#### toWatchList

- **GET** & `/towatchlist/entries` - requires an api key and returns all entries on the user's toWatchList (Note: this needs to include the basic movie information as well..think about what you'd need to display to show them their watch list)
- **POST** & `/towatchlist/entries` - requires an api key and all other data necessary for the toWatchList table, validates then inserts the data.
- **PUT** & `/towatchlist/entries/{id}` - requires an api key and all other data necessary for the toWatchList table and replaces the entire record in the database (if there is no record it should insert and return the appropriate HTTP code).
- **PATCH** & `/towatchlist/entries/{id}/priority` - requires an api key and new priority and updates the user's priority for the appropriate movie.
- **DELETE** & `/towatchlist/entries/{id}` - requires and api key and movieID and deletes the appropriate movie from the user's watchlist.

#### completedWatchList

- **GET** & `/completedwatchlist/entries` - requires an api key and returns all entries on the user's completedWatchList. (Note: this needs to include the basic movie information as well..think about what you'd need to display to show them their completed list)
- **GET** & `/completedwatchlist/entries/{id}/times-watched` - requires an api key and returns the number of times the user has watched the given movie
- **GET** & `/completedwatchlist/entries/{id}/rating` - requires an api key and returns the user's rating for this specific movie
- **POST** & `/completedwatchlist/entries` - requires an api key and all other data necessary for the completedWatchList table, validates then inserts the data. It should also recompute and update the rating for the appropriate movie.
- **PATCH** & `/completedwatchlist/entries/{id}/rating` - requires an api key and new rating and updates the rating for the appropriate movie in the completedWatchList table, then recalculates the movie's rating and updates the movies table.
- **PATCH** & `/completedwatchlist/entries/{id}/times-watched` - requires an api key and increments the number of times watched and updates the last date watched of the appropriate movie.
- **DELETE** & `/completedwatchlist /entries/{id}` - requires and api key and movieID and deletes the appropriate movie from the completedWatchList.

_Note:_ because the movie table contains an already-computed average rating, you need to recompute this average whenever a user adds or updates their rating. You can use the following formulas to determine the new rating.

##### Adding a new rating

<!-- Turn on your Markdown Preview to be able to read these formulas! -->

$$
\text{NewAvgRating} = \frac{
  (\text{OldAvgRating} \cdot \text{OldRatingCount}) + \text{NewRating}
}{
  \text{NewCount}
}
$$

##### Updating an existing rating

$$
\text{NewAvgRating} = \frac{
  (\text{OldAvgRating} \cdot \text{OldCount}) - \text{OldRating} + \text{NewRating}
}{
  \text{NewCount}
}
$$

#### Users

- **GET** & `/users/{id}/stats` - returns basic watching stats for the provided user. You can chose the stats, but you should have at least 4. e.g. total time watched, average score, planned time to watch, etc.

#### Auth

- **POST** & `/users/session` - accepts a username and password, verifies these credentials and returns the corresponding API key. (You can mostly steal this logic from your login page above, just generate json responses instead)

#### Filters

Extend up to four of the above GET endpoints to support filters. You should have at least four filters total, implemented across at least 3 different endpoints. This might include things like: filtering all movies by title or genre, toWatch movies by priority, most watched movies, best rated, etc.

## Testing

If you save all your testing to the end it will be **time-consuming**. I strongly recommend you take screenshots as you go. You would also be well served to label and save your thunder client requests in collections to make it easy to repeat your testing if you change something.

Keep in mind that you will only be able to test **GET** endpoints in the browser. All other endpoints will need to be tested with an API testing tool like ThunderClient.

Your final testing should all (even the GET endpoints) be done in your API testing tool, and for each endpoint you should include a couple of relevant screenshots to prove that the endpoint works (successfully and with errors).

Put all your testing screenshots in the _testing_screenshots_ folder and then compile them all, well labelled, into the _README.md_ file in the testing_screenshots folder.

## Submission

Make sure your remote Git repo is up-to-date and submit a link to your repo, plus a link to the live part of the site on Loki to Blackboard.
