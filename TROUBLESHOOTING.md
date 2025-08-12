# Troubleshooting Guide

## Common Issues and Solutions

### 1. Python 3.13 Compatibility Issues

**Problem**: You see an error like `AttributeError: 'FileFinder' object has no attribute 'find_module'`

**Cause**: Python 3.13 has known compatibility issues with snscrape due to changes in the import system.

**Solutions**:

#### Option A: Install Python 3.11 or 3.12 (Recommended)
```bash
# Install Python 3.11
brew install python@3.11

# Create a new virtual environment with Python 3.11
python3.11 -m venv venv311
source venv311/bin/activate
pip install snscrape

# Update the application to use the new virtual environment
# Edit the SnscrapeService.php file and change the venvPath to 'venv311'
```

#### Option B: Install snscrape from source
```bash
# Remove existing snscrape
pip uninstall snscrape

# Install from GitHub
pip install git+https://github.com/JustAnotherArchivist/snscrape.git
```

#### Option C: Use conda environment
```bash
# Install conda if you don't have it
brew install --cask anaconda

# Create a conda environment with Python 3.11
conda create -n snscrape python=3.11
conda activate snscrape
pip install snscrape
```

### 2. Pip Installation Issues

**Problem**: `error: externally-managed-environment`

**Cause**: macOS protects the system Python installation from package installations.

**Solutions**:

#### Option A: Use virtual environment (Recommended)
```bash
# Create virtual environment
python3 -m venv venv
source venv/bin/activate
pip install snscrape
```

#### Option B: Use pipx for applications
```bash
# Install pipx
brew install pipx

# Install snscrape
pipx install snscrape
```

#### Option C: Use --user flag
```bash
pip install --user snscrape
```

### 3. Snscrape Command Not Found

**Problem**: `zsh: command not found: snscrape`

**Solutions**:

#### Check if snscrape is installed
```bash
# Check in virtual environment
source venv/bin/activate
which snscrape

# Check system-wide
which snscrape
```

#### Install snscrape
```bash
# Using pip
pip install snscrape

# Using pipx
pipx install snscrape

# Using conda
conda install -c conda-forge snscrape
```

### 4. Database Migration Issues

**Problem**: Migration fails or database errors

**Solutions**:

#### Reset database
```bash
# Remove existing database
rm database/database.sqlite

# Run migrations
php artisan migrate

# Seed with demo data
php artisan db:seed --class=DemoJobSeeder
```

#### Check database permissions
```bash
# Set proper permissions
chmod 755 database/
chmod 644 database/database.sqlite
```

### 5. Laravel Application Issues

**Problem**: Application won't start or shows errors

**Solutions**:

#### Clear Laravel caches
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

#### Check PHP version
```bash
php --version
# Ensure you have PHP 8.1 or higher
```

#### Reinstall dependencies
```bash
rm -rf vendor/
composer install
```

### 6. Permission Issues

**Problem**: Permission denied errors

**Solutions**:

#### Set proper permissions
```bash
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
chmod -R 755 venv/
```

#### Check file ownership
```bash
# If using different user
sudo chown -R $USER:$USER .
```

### 7. Network/Proxy Issues

**Problem**: Snscrape can't connect to social media platforms

**Solutions**:

#### Check internet connection
```bash
ping google.com
```

#### Configure proxy (if needed)
```bash
# Set environment variables
export HTTP_PROXY=http://proxy.example.com:8080
export HTTPS_PROXY=http://proxy.example.com:8080
```

#### Use VPN if needed
Some platforms may block certain IP ranges.

### 8. Rate Limiting Issues

**Problem**: Social media platforms block requests

**Solutions**:

#### Add delays between requests
```bash
# Use snscrape with delays
snscrape --max-results 10 twitter-user username
```

#### Use different IP addresses
Consider using a VPN or proxy rotation.

#### Respect platform terms of service
Ensure you're following the platform's terms of service and rate limits.

### 9. Platform-Specific Issues

#### Twitter Issues
- Twitter may require authentication for some endpoints
- Consider using Twitter API for production use

#### Instagram Issues
- Instagram has strict rate limiting
- Some endpoints may require authentication

#### Reddit Issues
- Reddit uses Pushshift API which may have downtime
- Consider using Reddit API for production use

### 10. Application-Specific Issues

#### Job Execution Fails
1. Check the job error message in the web interface
2. Verify snscrape is working: `snscrape --help`
3. Test the command manually in terminal
4. Check logs: `tail -f storage/logs/laravel.log`

#### Data Not Displaying
1. Check if the job completed successfully
2. Verify database has data: `php artisan tinker`
3. Check for JavaScript errors in browser console

#### Export Issues
1. Ensure you have write permissions
2. Check available disk space
3. Verify the job has data to export

## Getting Help

### Check Logs
```bash
# Laravel logs
tail -f storage/logs/laravel.log

# System logs
tail -f /var/log/system.log
```

### Debug Mode
```bash
# Enable debug mode in .env
APP_DEBUG=true
```

### Community Resources
- [snscrape GitHub Issues](https://github.com/JustAnotherArchivist/snscrape/issues)
- [Laravel Documentation](https://laravel.com/docs)
- [Stack Overflow](https://stackoverflow.com)

### Report Issues
When reporting issues, please include:
1. Your operating system and version
2. Python version
3. PHP version
4. Laravel version
5. Error messages and logs
6. Steps to reproduce the issue
