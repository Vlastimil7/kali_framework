# Kali Framework

Easy MVC structure - Light Kali Framework for building websites or web applications

## 🚀 Stack

- **PHP 8+** - Backend logic
- **Vanilla JavaScript** - Frontend interactions
- **Tailwind CSS** - Styling framework
- **PHPMailer** - Email functionality
- **Node.js** - For Tailwind CSS compilation
- **Composer** - PHP dependency management

## 📋 Requirements

- PHP 8.0+
- Node.js 16+
- Composer 2.0+
- MySQL/MariaDB
- Web server (Apache/Nginx)

## 🛠️ Installation

### 1. Clone Repository

git clone https://github.com/your-username/project-name.git
cd project-name

### 2. Install Dependencies

# Install PHP dependencies

composer install

# Install Node dependencies

npm install

### 3. Environment Setup

# Copy environment template

cp .env.example .env

# Edit environment variables

nano .env

### 4. Configure Environment Variables

Edit `.env` file with your settings:

env

# Application

APP_ENV=development
APP_DEBUG=true

# Database

DB_HOST=localhost
DB_NAME=your_database_name
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

# URLs (adjust for your setup)

BASE_URL_DEV=/your-project/public
SITE_URL_DEV=http://localhost/your-project

# Production URLs

BASE_URL=
SITE_URL=https://your-domain.com

# Email Configuration

SMTP_HOST=smtp.gmail.com
SMTP_USERNAME=your_email@gmail.com
SMTP_PASSWORD=your_app_password
SMTP_PORT=587
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME=Your App Name

# reCAPTCHA

RECAPTCHA_SITE_KEY=your_recaptcha_site_key
RECAPTCHA_SECRET_KEY=your_recaptcha_secret_key

### 5. Configure .htaccess

**Option A: Manual Setup (Recommended)**

Add `public/.htaccess` to `.gitignore` and create manually:

**For Development:**
apache
RewriteEngine On
RewriteBase /your-project/public/
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.\*)$ index.php?url=$1 [QSA,L]

**For Production:**
apache
RewriteEngine On
RewriteBase /
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.\*)$ index.php?url=$1 [QSA,L]

### 6. Build Assets

# Development (with file watching)

npm run dev

# Production build

npm run build

### 7. Set Permissions (Linux/Mac)

chmod -R 755 .
chmod -R 775 storage/

## 🔧 Development

### Available Scripts

# Watch for Tailwind changes

npm run dev

# Build for production

npm run build

# Generate .htaccess (if using dynamic setup)

php generate-htaccess.php

### Project Structure

project/
├── .env # Environment variables (ignored by git)
├── .env.example # Environment template
├── public/ # Web root
│ ├── .htaccess # URL rewriting (ignored by git)
│ ├── index.php # Entry point
│ └── assets/ # Compiled assets
├── src/ # Application source
│ ├── API/ # Custom API for anything e.g chat bot
│ ├── config/ # Configuration files
│ ├── controllers/ # MVC Controllers
│ ├── models/ # MVC Models  
│ ├── views/ # MVC Views
│ ├── helpers/ # Helper functions
│ ├── middleware/ # Route middleware (auth, admin, ...)
│ └── routes/ # Route definitions
│ └── services/ # Service efinitions
├── storage/ # App storage
│ ├── cache/ # Cache files
│ ├── logs/ # Log files
│ └── uploads/ # File uploads
└── vendor/ # Composer dependencies (ignored by git)

### Route middleware

Protect one route with the fluent API:

```php
$router->get('profile', 'Front\\UserController@showProfile')
    ->middleware('auth');
```

Protect a whole route group:

```php
$router->group(['middleware' => ['auth', 'admin']], function (Core\Router $router): void {
    $router->get('admin/dashboard', 'Admin\\DashboardController@index');
    $router->get('admin/users', 'Admin\\UserController@index');
});
```

Custom middleware must implement `Core\MiddlewareInterface`. Register an alias with:

```php
$router->aliasMiddleware('verified', Middleware\VerifiedUserMiddleware::class);
```

### CSRF protection

Internal state-changing web routes use the `csrf` middleware:

```php
$router->post('profile/update', 'Front\\UserController@updateProfile')
    ->middleware(['auth', 'csrf']);
```

POST forms rendered through the main layout receive a hidden `_token` field automatically. For forms outside the main content, use:

```php
<?= csrf_field() ?>
```

The current token is also available for AJAX requests:

```js
const token = document.querySelector('meta[name="csrf-token"]').content;

fetch('/account/update', {
  method: 'POST',
  headers: { 'X-CSRF-TOKEN': token },
});
```

External payment callbacks and stateless API endpoints should not use session CSRF middleware; they need their own signature or API-token verification.

### Request object

Type-hint `Core\Request` as the first controller argument. The router injects it automatically, including route parameters:

```php
use Core\Request;

public function update(Request $request, int $id)
{
    $email = $request->string('email');
    $page = $request->int('page', 1);
    $enabled = $request->boolean('enabled');
    $attachment = $request->file('attachment');
}
```

Common methods include `input()`, `post()`, `query()`, `string()`, `int()`, `boolean()`, `has()`, `filled()`, `only()`, `except()`, `file()`, `header()`, `cookie()`, `route()`, `ip()`, `uri()`, and `isSecure()`.

### Validation

Create a validator from request data and declarative rules. Failed forms can store validation errors, safe old input, and an error toast in one call:

```php
use Helpers\Validator;

$validator = Validator::make($request->post(), [
    'email' => 'bail|required|email|max:254',
    'password' => 'bail|required|string|min:8',
    'role' => 'required|in:user,admin',
], [
    'email.required' => 'Zadejte e-mail.',
], [
    'email' => 'e-mail',
]);

if ($validator->fails()) {
    $validator->flash('registration', $request->post());
    header('Location: ' . locale_url('register'));
    exit;
}

$data = $validator->validated();
```

Use `Flash::old('registration')` to refill the form and `Validator::flashedErrors('registration')` for inline errors. Passwords, tokens, and other sensitive fields are removed from old input automatically.

Available rules include `required`, `required_if`, `required_with`, `accepted`, `string`, `integer`, `numeric`, `boolean`, `array`, `email`, `url`, `min`, `max`, `between`, `size`, `in`, `not_in`, `same`, `different`, `confirmed`, `regex`, `date`, `date_format`, `alpha`, `alpha_num`, and `slug`. Modifiers `bail`, `sometimes`, and `nullable` are supported. An array of rules can also contain a closure returning `true`, an error string, or `false`.

## LOGGER

HOW TO USE IT IN PROJECT

1. Use a helper - use Helpers/Logger;
2. Use a function from Logger fyi:  Logger::error('LogoBrief - PDF render failed', ['error' => $e->getMessage()]);

## 🚀 Deployment

### Automatic Deployment with GitHub Actions

This project includes automated deployment setup:

1. **Setup Server Requirements:**

   - Ubuntu/Debian server with SSH access
   - PHP 8+, Composer, Node.js, Nginx/Apache
   - Create deploy user with sudo access

2. **Configure GitHub Secrets:**
   Go to Repository Settings → Secrets and Variables → Actions:

   - `SSH_PRIVATE_KEY`: Server SSH private key
   - `SERVER_HOST`: Server IP or domain
   - `SERVER_USER`: Deploy username (usually 'deploy')

3. **Server Setup:**

   # Create deploy user

   sudo adduser deploy
   sudo usermod -aG www-data deploy
   sudo usermod -aG sudo deploy

   # Generate SSH keys

   sudo su - deploy
   ssh-keygen -t rsa -b 4096

   # Setup project directory

   sudo mkdir -p /var/www/projekty/your-project
   sudo chown deploy:www-data /var/www/projekty/your-project

4. **Deploy Workflow:**
   - Push to `main` branch triggers automatic deployment
   - Installs dependencies, builds assets, updates server
   - Monitor deployment in GitHub Actions tab

### Manual Deployment

# On server

cd /var/www/your-project
git pull origin main
composer install --no-dev --optimize-autoloader
npm ci --production
npm run build
sudo systemctl reload php8.2-fpm nginx

## 🔒 Security Notes

- Never commit `.env` files to repository
- Use strong database passwords in production
- Configure proper file permissions on server
- Enable HTTPS in production
- Keep dependencies updated

## 🐛 Troubleshooting

### Common Issues

**1. Permission Denied Errors:**

sudo chown -R www-data:www-data storage/
sudo chmod -R 775 storage/

**2. Assets Not Loading:**

- Check .htaccess RewriteBase path
- Verify BASE_URL in .env matches your setup
- Run `npm run build` to compile assets

**3. Database Connection Failed:**

- Verify database credentials in .env
- Check if database exists
- Ensure MySQL/MariaDB is running

**4. 500 Internal Server Error:**

- Check error logs in `storage/logs/`
- Verify PHP error reporting settings
- Check file permissions

## 📞 Support

- Create an issue for bugs or feature requests
- Check existing issues before creating new ones
- Provide detailed error messages and steps to reproduce

## 📄 License

This project is open source and available under the [MIT License](LICENSE).

Deployment fixed and tested
