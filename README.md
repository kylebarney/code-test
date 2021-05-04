# Setup

1. Fork the repo
2. Copy the file `.env.example` to `.env`
3. Run `composer install`
4. Run `php artisan key:generate`
5. Run `./vendor/bin/sail up -d` (This repository uses Docker and Laravel Sail for a quick setup. You can run it on another environment, but you'll need to configure it yourself. *Note for Windows users: This project will run much faster if you clone and run this solely within the WSL environment instead of running it off of the Windows filesystem mounted into WSL.*)
6. `exec` into the Docker container running Laravel and run `vendor/bin/phpunit` to execute the tests that hit the API.

# Project and Key File Overview

This project uses Laravel Sanctum to provide a lightweight authentication scheme for API requests.

User registration is outside the scope of this project.

- `routes/api.php` => API Route Definitions
- `app/Http/Middleware/ForceJsonResponse.php` => Middleware that ensures that we send back a JSON response to any request on an `api` endpoint, even if the request was missing the appropriate header (project requirement).
- `app/Http/Kernel.php` => `Sanctum` authentication registration for API endpoints
- `app/Http/Controllers/ProductsController.php` => Controller that handles requests for product data from the API. Includes request validation.
- `app/Models/User` and `app/Models/Product` => Contain the Eloquent ORM models along with their relations and other attributes.
- `database/migrations/` => Contains the database migrations for this project.
- `tests/Feature/ProductsControllerTest.php` => Contains tests for the products controllers, covering authentication, validation, file uploads, etc.

# Project Requirements

- Create a simple RESTful API written in Laravel/PHP.
- The project contains **users** and **products**. A user will have the ability to add & remove products.

## Users

Each user must have, but is not limited to:

- ID
- First Name
- Last Name
- Email (unique)

**Please note:**

- These users are the only users that are able to make requests via the API.
- User creation/maintenance is **not** done through the API.

## Products

Each product must have, but is not limited to:

- ID
- Name
- Description
- Price
- Image

## Database

- MySQL
- All tables in the database must be created programatically

## Authentication

You must implement an authentication system so that the API knows which of the users is making the request. All requests should ensure that an authorized user is making the request. In the event of an unauthorized user, an error should be thrown.

## Requests

The following requests should be implemented:

- Add product
    - All fields required except ID and image
- Update product
    - All fields required except image
- Delete product
- Get product
- Upload product image
- Get list of all products
- Attach product to requesting user
- Remove product from requesting user
- List products attached to requesting user

## Tests

You must write tests to back up your code. You are free to use any testing tools or frameworks you like.
