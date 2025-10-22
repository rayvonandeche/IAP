# Bomatrack App

## Overview
Bomatrack is a PHP web application framework designed for managing user authentication and dashboard functionalities. This project is built using Object-Oriented Programming principles and follows a structured MVC (Model-View-Controller) architecture.

## Project Structure
The project is organized into several directories, each serving a specific purpose:

- **app**: Contains the core application logic, including controllers, models, views, core classes, and helper functions.
  - **Controllers**: Handles user requests and application logic.
  - **Models**: Represents the data structure and database interactions.
  - **Views**: Contains the HTML templates for rendering the user interface.
  - **Core**: Includes essential classes for application initialization and routing.
  - **Helpers**: Provides utility functions for authentication and validation.

- **public**: The entry point of the application, containing publicly accessible files such as CSS and JavaScript.
- **config**: Holds configuration files for application settings and database connections.
- **.htaccess**: Used for URL rewriting and routing.
- **composer.json**: Manages project dependencies and autoloading settings.

## Installation
1. Clone the repository to your local machine.
2. Navigate to the project directory.
3. Run `composer install` to install dependencies.
4. Configure your database settings in `config/database.php`.
5. Access the application via your web server.

## Commit Responsibilities
- **Team Member 1**: Create the project structure and initial files.
  - Commit: "Initial project structure created with basic files."
  
- **Team Member 2**: Implement the Core classes (App, Database, Router).
  - Commit: "Core classes implemented for application initialization and routing."
  
- **Team Member 3**: Develop the Models (User, Item) and BaseModel.
  - Commit: "Models created for User and Item with database interaction methods."
  
- **Team Member 4**: Create Controllers (AuthController, UserController, DashboardController).
  - Commit: "Controllers implemented for user authentication and dashboard management."
  
- **Team Member 5**: Build Views (auth, dashboard, layouts) and public entry point.
  - Commit: "Views created for authentication and dashboard, entry point set up."

## Usage
- Navigate to the login page to authenticate users.
- Once logged in, users can access their dashboard and profile information.

## License
This project is licensed under the MIT License. See the LICENSE file for more details.