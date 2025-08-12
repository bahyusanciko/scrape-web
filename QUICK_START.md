# Quick Start Guide

## Prerequisites

- PHP 8.1+
- Composer
- Python 3.8+
- snscrape

## Installation

### Option 1: Automated Installation (Recommended)

```bash
# Make the install script executable
chmod +x install.sh

# Run the installation script
./install.sh
```

### Option 2: Manual Installation

```bash
# Install PHP dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Set up database
touch database/database.sqlite
php artisan migrate

# Install snscrape
pip install snscrape

# Set permissions
chmod -R 755 storage bootstrap/cache
```

## Start the Application

```bash
php artisan serve
```

Visit `http://localhost:8000` in your browser.

## Demo Data

To populate the database with demo data:

```bash
php artisan db:seed --class=DemoJobSeeder
```

## Quick Test

1. Go to the dashboard
2. Click "New Scraping Job"
3. Select "Twitter" as platform
4. Choose "User" as scraper type
5. Enter "textfiles" as the target
6. Set max results to 5
7. Check "Execute job immediately"
8. Click "Create Job"

## Features Overview

### Dashboard
- Overview of all scraping activities
- Statistics and platform breakdown
- Recent jobs and data
- Snscrape installation status

### Scraping Jobs
- Create new scraping jobs
- Monitor job status (pending, running, completed, failed)
- Execute jobs manually
- Retry failed jobs
- Delete jobs

### Data Management
- Browse all scraped data
- View data by platform
- Export data in JSON/CSV formats
- Filter and search data

### Supported Platforms
- **Twitter**: Users, hashtags, searches, lists
- **Instagram**: Users, hashtags, locations
- **Facebook**: Users, groups, communities
- **Reddit**: Users, subreddits, searches
- **Telegram**: Channels
- **Mastodon**: Users, toots

## Troubleshooting

### Snscrape Not Found
```bash
pip install snscrape
```

### Database Issues
```bash
php artisan migrate:fresh
php artisan db:seed --class=DemoJobSeeder
```

### Permission Issues
```bash
chmod -R 755 storage bootstrap/cache
```

## Next Steps

1. **Explore the Interface**: Navigate through the dashboard, jobs, and data sections
2. **Create Your First Job**: Try scraping data from your favorite social media platform
3. **Export Data**: Download scraped data for analysis
4. **Customize**: Modify the application to suit your specific needs

## Support

- Check the main README.md for detailed documentation
- Review the snscrape documentation for platform-specific options
- Open an issue on the repository for bugs or feature requests
