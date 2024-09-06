# LevelZ Assignment

## Description
This Laravel project, developed by [Rasoul Rahimi](https://www.linkedin.com/in/rasoulrahimii/) as part of the LevelZ hiring process, allows the user to register in the system through a multi-step process based on the requirements of the assignment.

## How to run

1. Make a copy of `.env.example`:
```shell
cp .env.example .env
```
2. Start the Docker containers:
```shell
./vendor/bin/sail up -d
```
3. Migrate the database:
```shell
./vendor/bin/sail artisan migrate
```
4. Import the `Level Z Assignment.postman_collection.json` file, located in the root directory of the project, into the Postman app. It makes it easy to see sample requests and responses, and helps you to test the project easily.

## Running Tests
Execute the following command to run tests:
```shell
./vendor/bin/sail test
```

## %100 Test Coverage
Execute the following command to see the test coverage for this project:
```shell
./vendor/bin/sail test --coverage
```

## Justifications

**Why are there minor modifications to the application requirements in my code?**

Some changes, such as not returning an encrypted (or hashed) PIN, were made because they offer no benefit and are not the best security practice. Similarly, returning the full user object is not ideal. If you need further clarification on these decisions, I’m happy to explain. :)

**Why did you choose to place the logic in the controller instead of distributing it across other layers in the application? (e.g. action classes, service layers, repository layers, etc.)**

Given the simplicity and minimal nature of the application’s requirements, adding extra layers might have been unnecessary and could lead to over-engineering. Additionally, using short invokable controllers without introducing unnecessary abstractions, along with API Resources and Form Requests to separate concerns, streamlines the code further and makes it more concise. This approach ensures that we meet the project’s needs efficiently.

**Usage of Direct Insertion of Factories Instead of Model::factory:**

I prefer minimizing the use of magic like the one with`Model::factory` with `HasFactory` trait, hence the direct insertion of factories are preferred.

**Why aren’t there common migration files (e.g., for users, jobs, etc.) in this Laravel project?**

Due to the lack of specifications for this project, I aimed to keep everything as minimal as possible.

**Why is there no `status` key in the API responses?**

The HTTP response status in the header effectively conveys the status. Additionally, a `message` key is included in all responses for further explanation.
