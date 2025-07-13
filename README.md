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
RewriteRule ^(.*)$ index.php?url=$1 [QSA,L]


**For Production:**
apache
RewriteEngine On
RewriteBase /
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php?url=$1 [QSA,L]


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
├── .env                    # Environment variables (ignored by git)
├── .env.example           # Environment template
├── public/                # Web root
│   ├── .htaccess         # URL rewriting (ignored by git)
│   ├── index.php         # Entry point
│   └── assets/           # Compiled assets
├── src/                  # Application source
│   ├── config/           # Configuration files
│   ├── controllers/      # MVC Controllers
│   ├── models/          # MVC Models  
│   ├── views/           # MVC Views
│   ├── helpers/         # Helper functions
│   └── routes/          # Route definitions
├── storage/             # App storage
│   ├── cache/          # Cache files
│   ├── logs/           # Log files
│   └── uploads/        # File uploads
└── vendor/             # Composer dependencies (ignored by git)


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

Test automatic deployment - fixed