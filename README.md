# Symfony-Tasks

## Description

Symfony-Tasks is a web application that allows users to create projects and manage tasks within those projects. Users can create tasks, assign them to specific categories, move tasks between categories, and delete tasks.

## Requirements

To run the Symfony-Shortly project, you need to have the following tools installed:

- PHP (recommended version: 7.4 or higher)
- Composer
- Symfony CLI

## Features

- User registration and authentication: Users can create an account and log in to the application.
- Project creation: Users can create projects and assign tasks to those projects.
- Task management: Users can create, update, and delete tasks. Tasks can be assigned to specific categories and moved between categories.
- User-friendly interface: The application provides an intuitive and user-friendly interface for easy navigation and task management.

## Installation

1. Clone the project repository:

   ```
   git clone https://github.com/eastcause/symfony-tasks.git
   ```
2. Configure the database connection in the `.env` file.
3. Run the migrations to create the database schema:
   ```
   php bin/console doctrine:migrations:migrate
   ```

## Usage

- Register a new user account or log in with an existing account.
- Create a project by providing a project name and any other necessary details.
- Add tasks to the project by specifying description, and category.
- Manage tasks by moving them between categories or deleting them as needed.
- Create, update, and delete categories to organize tasks effectively.
