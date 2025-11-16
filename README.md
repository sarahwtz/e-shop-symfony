# E-Shop Symfony

## About

Business application with **dual user roles**: customers and administrators.

- **Customers** can browse products, manage their cart, place orders via Stripe, and track their purchase history.
- **Administrators** have a dedicated panel to manage products, categories, and customer orders.

The system also includes **automatic order confirmation emails** and **role-based access control**.

---

## Features

- **Dual user roles**: Customers and Administrators.
- **Customer panel**:
  - Browse products by category
  - Add, update, and remove items from cart
  - Checkout via Stripe
  - View order history and track total spending
- **Administrator panel**:
  - Manage products and categories (CRUD)
  - View and manage customer orders
- **Authentication and authorization**:
  - Registration and login
  - Email verification
  - Role-based access control
- **Automatic order confirmation emails** after successful payment
- **Database management** with Doctrine ORM, migrations, and fixtures
- **Responsive frontend** using Tailwind CSS

---

## Technologies

- PHP 8.2, Symfony 7
- Twig
- Tailwind CSS
- Stripe PHP SDK
- Symfony Mailer
- Doctrine ORM
- MySQL
- Fixtures & Faker
- Composer
- Symfony CLI

---

## Screenshots

### Home / Product List
![Home / Product List](screenshots.eshop_symfony/eshop_products.png)

### Admin Dashboard
![Admin Dashboard](screenshots.eshop_symfony/admin_panel.png)

### Customer Dashboard
![Customer Dashboard](screenshots.eshop_symfony/customer_dashboard.png)

---

# Installation & Setup

Follow these steps to set up and run the project:

```bash
# Clone the repository
git clone https://github.com/sarahwtz/e-shop-symfony.git
cd e-shop-symfony

# Install PHP and Composer
php -v
composer -V

# Install project dependencies
composer install

# Setup environment file
cp .env.example .env

# Generate a new APP_SECRET and set your database credentials
# (edit .env file with your values)
export APP_SECRET=<your_generated_secret>
export DATABASE_URL="mysql://username:password@127.0.0.1:3306/e_shop?serverVersion=8.0&charset=utf8mb4"


# Run database migrations and load fixtures
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load

# Serve the application locally
symfony server:start
```


## Database Structure
---------------------

### user

| Column       | Type         | Description                     |
|-------------|--------------|---------------------------------|
| id          | int          | Unique identifier for each user |
| email       | varchar(180) | User email                      |
| roles       | json         | Array of user roles             |
| password    | varchar(255) | Hashed password                 |
| full_name   | varchar(255) | User full name (optional)       |
| address     | text         | User address (optional)         |
| is_verified | boolean      | Whether the email is verified   |

**Indexes / Constraints:**  
- Primary key on `id`  
- Unique index on `email`  

---

### category

| Column | Type         | Description       |
|--------|--------------|-----------------|
| id     | int          | Category ID       |
| name   | varchar(255) | Category name     |
| description | text    | Category description |

**Indexes / Constraints:**  
- Primary key on `id`  

---

### product

| Column      | Type         | Description                  |
|------------|--------------|------------------------------|
| id         | int          | Product ID                   |
| category_id| int          | Reference to category        |
| name       | varchar(255) | Product name                 |
| description| text         | Product description          |
| price      | double       | Product price                |
| image      | varchar(255) | Image filename/path          |
| created_at | datetime     | Creation timestamp           |
| updated_at | datetime     | Last update timestamp (nullable) |

**Indexes / Constraints:**  
- Primary key on `id`  
- Foreign key: `category_id` → `category(id)`  

**Relationships:**  
- Product → Category (N:1)  

---

### order

| Column          | Type         | Description                     |
|----------------|--------------|---------------------------------|
| id             | int          | Order ID                        |
| user_name      | varchar(255) | Name of the customer            |
| user_email     | varchar(255) | Email of the customer           |
| user_address   | varchar(255) | Address of the customer         |
| total          | double       | Total order amount              |
| payment_method | varchar(255) | Payment method                  |
| payment_status | varchar(255) | Status of payment               |
| is_pending     | boolean      | Order is pending (nullable)     |
| is_shipped     | boolean      | Order is shipped (nullable)     |
| is_delivered   | boolean      | Order is delivered (nullable)   |
| is_processed   | boolean      | Order is processed (nullable)   |
| order_reference| varchar(255) | Unique order reference          |
| order_date     | datetime     | Date of the order               |

**Indexes / Constraints:**  
- Primary key on `id`  

**Relationships:**  
- Order → CartHistory (1:N)  

---

### cart_history

| Column         | Type         | Description                       |
|---------------|--------------|-----------------------------------|
| id             | int          | Cart history ID                  |
| order_id       | int          | Reference to order               |
| product_name   | varchar(255) | Name of the product              |
| product_price  | float        | Price of the product at purchase |
| quantity       | int          | Quantity purchased               |
| sub_total      | float        | Subtotal for this item           |
| order_reference| varchar(255) | Reference of the order           |

**Indexes / Constraints:**  
- Primary key on `id`  
- Foreign key: `order_id` → `order(id)`  

**Relationships:**  
- CartHistory → Order (N:1)  

---

### messenger_messages

| Column        | Type          | Description                        |
|---------------|---------------|------------------------------------|
| id            | bigint        | Message ID                          |
| body          | text          | Message content                     |
| headers       | text          | Message headers                     |
| queue_name    | varchar(190)  | Name of the queue                   |
| created_at    | datetime      | When the message was created        |
| available_at  | datetime      | When the message became available   |
| delivered_at  | datetime      | When the message was delivered (nullable) |

**Indexes / Constraints:**  
- Primary key on `id`  
- Indexes on `queue_name`, `available_at`, `delivered_at`  



