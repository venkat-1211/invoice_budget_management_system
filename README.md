# Project Setup

## Requirements

* PHP **8.3** or higher
* Composer
* MySQL / MariaDB
* Laravel supported extensions

## Installation

1. Clone the repository:

   ```bash
   git clone https://github.com/venkat-1211/invoice_budget_management_system.git
   ```

2. Navigate to the project directory:

   ```bash
   cd your-repository
   ```

3. Install PHP dependencies:

   ```bash
   composer install
   ```

   > If you face dependency issues, you can use:
   >
   > ```bash
   > composer update
   > ```

4. Copy the environment file:

   ```bash
   cp .env.example .env
   ```

5. Generate the application key:

   ```bash
   php artisan key:generate
   ```

6. Configure your database credentials in the `.env` file.

7. Run database migrations:

   ```bash
   php artisan migrate
   ```

8. Seed the database:

   ```bash
   php artisan db:seed
   ```

9. Start the development server:

   ```bash
   php artisan serve
   ```

The application will be available at:

```
http://127.0.0.1:8000
```

---

# Development Notes

This project follows advanced Laravel development practices with a clean and scalable architecture.

Since this is a product-based project, the codebase includes advanced implementations and design patterns. I can explain any module or code section line by line, as I have completed the official Laracasts Laravel series and have applied those best practices throughout the project.
