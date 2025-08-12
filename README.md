# Scrape Web Application

A powerful Laravel web application that delivers an intuitive, user-friendly interface for advanced social media scraping using the robusts Python library. Effortlessly collect and analyze data from multiple social media platforms through a seamless and efficient web experience.

## Features

- **Multi-Platform Support**: Scrape data from Twitter, Instagram, Facebook, Reddit, Telegram, Mastodon, and more
- **Job Management**: Create, monitor, and manage scraping jobs
- **Data Visualization**: View and analyze scraped data with statistics and insights
- **Export Options**: Export data in JSON and CSV formats
- **Real-time Status**: Monitor job progress and execution status
- **Modern UI**: Beautiful, responsive interface built with Tailwind CSS and Alpine.js

## Supported Platforms

- **Twitter**: Users, hashtags, searches, lists
- **Instagram**: Users, hashtags, locations
- **Facebook**: Users, groups, communities
- **Reddit**: Users, subreddits, searches
- **Telegram**: Channels
- **Mastodon**: Users, toots

## Prerequisites

- PHP 8.1 or higher
- Composer
- Python 3.8 or higher
- snscrape (Python package)

## Installation

### 1. Clone the Repository

```bash
git clone https://github.com/bahyusanciko/scrape-web
cd scrape-web
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install snscrape

```bash
pip install snscrape
```

### 4. Environment Setup

```bash
cp .env.example .env
php artisan key:generate
```

Update the `.env` file with your database configuration:

```env
DB_CONNECTION=sqlite
DB_DATABASE=/path/to/your/database.sqlite
```

### 5. Database Setup

```bash
php artisan migrate
```

### 6. Start the Development Server

```bash
php artisan serve
```

The application will be available at `http://localhost:8000`

## Usage

### Creating a Scraping Job

1. Navigate to the "Scraping" section
2. Click "Create New Job"
3. Select the platform (e.g., Twitter)
4. Choose the scraper type (e.g., user, hashtag, search)
5. Enter the target (e.g., username, hashtag, search term)
6. Optionally set a maximum number of results
7. Choose whether to execute immediately or later
8. Click "Create Job"

### Managing Jobs

- **View Jobs**: See all your scraping jobs with their status
- **Execute Jobs**: Run pending jobs manually
- **Retry Failed Jobs**: Retry jobs that have failed
- **Delete Jobs**: Remove jobs you no longer need

### Viewing Data

- **Dashboard**: Overview of all scraping activities
- **Data Browser**: Browse all scraped data with filtering options
- **Export Data**: Download data in JSON or CSV format

## API Endpoints

The application also provides API endpoints for programmatic access:

### Jobs

- `GET /scraping/jobs` - List all jobs
- `POST /scraping/jobs` - Create a new job
- `GET /scraping/jobs/{id}` - Get job details
- `POST /scraping/jobs/{id}/execute` - Execute a job
- `DELETE /scraping/jobs/{id}` - Delete a job

### Data

- `GET /scraping/jobs/{id}/data` - Get data for a specific job
- `GET /scraping/jobs/{id}/export` - Export job data

### Status

- `GET /scraping/status` - Get overall scraping statistics

## Configuration

### Snscrape Settings

The application automatically detects if snscrape is installed. You can configure additional snscrape options in the job creation form.

### Database

The application uses SQLite by default, but you can configure it to use MySQL or PostgreSQL by updating the database configuration in `.env`.

### Queue Configuration

For better performance with large scraping jobs, consider setting up a queue system:

```bash
# Install Redis (recommended)
# Configure queue driver in .env
QUEUE_CONNECTION=redis

# Start queue worker
php artisan queue:work
```

## Security Considerations

- The application runs snscrape commands on your server
- Ensure proper access controls are in place
- Consider rate limiting for scraping operations
- Be aware of platform terms of service and rate limits

## Troubleshooting

### Snscrape Not Found

If the application shows "Snscrape Not Installed":

1. Ensure Python 3.8+ is installed
2. Install snscrape: `pip install snscrape`
3. Verify installation: `snscrape --help`

### Job Execution Failures

Common issues:

1. **Network Issues**: Check your internet connection
2. **Rate Limiting**: Some platforms may rate limit requests
3. **Invalid Targets**: Ensure the target format is correct
4. **Platform Changes**: Social media platforms may change their APIs

### Database Issues

If you encounter database errors:

1. Check database permissions
2. Ensure migrations are run: `php artisan migrate`
3. Verify database configuration in `.env`

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Acknowledgments

- [snscrape](https://github.com/JustAnotherArchivist/snscrape) - The underlying scraping tool
- [Laravel](https://laravel.com/) - The PHP framework
- [Tailwind CSS](https://tailwindcss.com/) - The CSS framework
- [Alpine.js](https://alpinejs.dev/) - The JavaScript framework

## Support

For issues and questions:

1. Check the troubleshooting section
2. Review the snscrape documentation
3. Open an issue on the repository
4. Check Laravel documentation for framework-specific questions
