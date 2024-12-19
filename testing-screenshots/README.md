# Testing - Hikmatullah Hussain Zada, Maryam Alchoib

Please be sure to put all your testing-screenshots in this folder so they won't sync to Loki. You can assemble your testing document below. It only need to include well labelled screenshots of your testing (no code).

## Create Account Testing

This is the Create Account Page
![](./createAccount.png)

Submitting without Email
![](./createAccount2.png)

Submitting with wrong email format
![](./createAccount3.png)

Submitting without Password
![](./createAccount4.png)

Submitting with wrong password format
![](./createAccount5.png)

Account Successfully Created
![](./createAccount6.png)

The Account in the database
![](./createAccount7.png)

## Index Page Testing

Index Page
![](./index.png)
![](./index2.png)
![](./index3.png)

## Login Page Testing

Login page
![](./login.png)

Submitting empty inputs
![](./login2.png)

Submitting empty password
![](./login3.png)

Submitting wrong inputs
![](./login4.png)

## View Account Testing 

View Account Page
![](./viewAccount.png)

Regenerated API Key
![](./viewAccount2.png)
![](./viewAccount3.png)

# API Endpoints

## GET Method

Endpoints:

## `/movies`
![](./endpoint.png)
![](./endpoint2.png)
![](./endpoint3.png)

## `/movies/{id}`
![](./endpoint4.png)
![](./endpoint5.png)
Invalid input
![](./endpoint6.png)
![](./endpoint7.png)

## `/movies/{id}/rating`
![](./endpoint8.png)
![](./endpoint9.png)
Invalid
![](./endpoint10.png)
![](./endpoint11.png)

## `/towatchlist/entries`
![](./endpoint12.png) 
empty
![](./endpoint13.png)
Invalid API
![](./endpoint14.png)
![](./endpoint15.png)
Test with valid inputs
![](./endpoint52.png)

## `/completedwatchlist/entries`
![](./endpoint16.png) 
empty
![](./endpoint17.png)
Invalid API
![](./endpoint18.png)
Test with valid inputs
![](./endpoint53.png)

## `/completedwatchlist/entries/{id}/times-watched`
![](./endpoint19.png)
![](./endpoint20.png) 
Invalid API
![](./endpoint21.png)
Test with valid inputs
![](./endpoint54.png)


## `/completedwatchlist/entries/{id}/rating`
![](./endpoint22.png)
![](./endpoint23.png)
Invalid API
![](./endpoint24.png)
Test with valid inputs
![](./endpoint55.png)

## `/users/{id}/stats`
![](./endpoint25.png)
empty stats
![](./endpoint26.png)
Invalid User
![](./endpoint27.png)
![](./endpoint28.png)
Test with full entries
![](./endpoint56.png)

## POST Method

## `/towatchlist/entries`
![](./endpoint29.png)
![](./endpoint30.png)
Invalid input
![](./endpoint31.png)

## `/completedwatchlist/entries`
![](./endpoint32.png)
![](./endpoint33.png)

## `/users/session`
![](./endpoint34.png)
![](./endpoint35.png)
Wrong username or password
![](./endpoint36.png)

## PUT Method

## `/towatchlist/entries/{id}`
![](./endpoint37.png)
![](./endpoint38.png)

## PATCH Methods

## `/towatchlist/entries/{id}/priority`
![](./endpoint39.png)
![](./endpoint40.png)

## `/completedwatchlist/entries/{id}/rating`
![](./endpoint41.png)
![](./endpoint42.png)

## `/completedwatchlist/entries/{id}/times-watched`
![](./endpoint43.png)
![](./endpoint44.png)

## DELETE Methods

## `/towatchlist/entries/{id}`
![](./endpoint45.png)
![](./endpoint46.png)
Invalid Entry
![](./endpoint47.png)
![](./endpoint48.png)

## `/completedwatchlist/entries/{id}`
![](./endpoint49.png)
![](./endpoint50.png)
Invalid Entry
![](./endpoint51.png)

## Invalid Endpoint Test

![](./endpointfail.png)
