# Twiva - Laravel E-Commerce Platform

Twiva is a modern, modular e-commerce platform built with Laravel and React. It follows
a domain-driven architecture using the `nwidart/laravel-modules` package to organize
functionality into separate modules.

## Project Structure

```text
.
├── Modules/                     # Domain modules (User, Product, Order, etc.)
│   ├── Admin/                   # Admin panel functionality
│   ├── User/                    # User authentication and profiles
│   ├── Product/                 # Product catalog and management
│   ├── Order/                   # Order processing and history
│   ├── Business/                # Business logic and operations
│   ├── Category/                # Product categorization
│   └── Notification/            # Notification system
├── app/                         # Core Laravel application
├── ecommerce-ui/                # React frontend application
├── resources/                   # Blade views and assets
├── routes/                      # API and web routes
└── config/                      # Laravel configuration files
```

## Features

- Modular architecture for easy maintenance and scalability
- Admin panel for managing products, orders, and users
- User authentication and profile management
- Product catalog with categories
- Order processing system
- Notification system
- RESTful API for frontend integration
- Modern React frontend with TypeScript
- TailwindCSS for styling

## Tech Stack

- **Backend**: Laravel 12, PHP 8.2+
- **Frontend**: React 18, TypeScript, TailwindCSS
- **Database**: MySQL/PostgreSQL/SQLite
- **Authentication**: Laravel Sanctum, JWT
- **Module Management**: nwidart/laravel-modules
- **Build Tools**: Vite 6, npm

## Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js 16+ and npm
- Database (MySQL, PostgreSQL, or SQLite)

## Installation

1. Clone the repository:

   ```bash
   git clone <repository-url>
   cd Twiva
   ```

2. Install PHP dependencies:

   ```bash
   composer install
   ```

3. Install frontend dependencies:

   ```bash
   npm install
   ```

4. Set up the environment file:

   ```bash
   cp .env.example .env
   ```

5. Generate application key:

   ```bash
   php artisan key:generate
   ```

6. Configure your database in the [.env](file:///home/panda/GitHub_Projects/Twiva/.env.example#L11-L14)
   file, then run migrations:

   ```bash
   php artisan migrate
   ```

7. Install and compile frontend assets:

   ```bash
   cd ecommerce-ui
   npm install
   cd ..
   ```

## Running the Application

### Development Mode

To run the application in development mode with all services:

```bash
composer run dev
```

This command starts:

- Laravel development server
- Queue worker
- Real-time log viewer
- Vite frontend development server

### Frontend Development

To work specifically on the React frontend:

```bash
cd ecommerce-ui
npm run dev
```

### Building for Production

To build the application for production:

1. Build the React frontend:

   ```bash
   cd ecommerce-ui
   npm run build
   cd ..
   ```

2. Build Laravel assets:

   ```bash
   npm run build
   ```

## Module Structure

Each module in the [Modules/](file:///home/panda/GitHub_Projects/Twiva/Modules) directory
follows a consistent structure:

- `app/` - Contains controllers, models, and other PHP code
- `database/` - Migrations, seeders, and factories
- `resources/` - Views and language files
- `routes/` - Module-specific routes
- `config/` - Module configuration
- `package.json` - Frontend dependencies (if applicable)

## API Documentation

The API routes are defined in [routes/api.php](file:///home/panda/GitHub_Projects/Twiva/routes/api.php)
and module-specific API routes in each module's `routes/api.php` file.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This project is open-sourced software licensed under the
[MIT license](https://opensource.org/licenses/MIT).
