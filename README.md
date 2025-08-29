# SocialScheduler

**SocialScheduler** is a powerful social media automation tool built with **Vue 3** and **Laravel**. It allows users to bulk schedule posts across multiple platforms, automate engagement, and track post performance—all from a single dashboard.

## Features
- **Bulk Scheduling** – Schedule multiple posts in advance for Facebook, Instagram, Twitter, and WhatsApp.
- **Multi-Platform Posting** – Publish content to multiple social media accounts simultaneously.
- **Post Calendar** – Visualize and manage scheduled posts with a drag-and-drop interface.
- **AI-Powered Caption Suggestions** – Generate engaging captions with AI assistance.
- **Engagement Analytics** – Track likes, comments, shares, and overall post performance.
- **Automated Follow-ups** – Set up auto-replies for comments and DMs.
- **Media Library** – Store and reuse images, videos, and captions.
- **Export Reports** – Download engagement analytics as CSV or PDF.

## Tech Stack
- **Frontend**: Vue 3 + Vite
- **Backend**: Laravel
- **Database**: MySQL
- **Deployment**: Docker, Nginx

## Recommended Development Setup
To get started, use the following tools:
- [VS Code](https://code.visualstudio.com/) with [Volar](https://marketplace.visualstudio.com/items?itemName=johnsoncodehk.volar) (disable Vetur if installed).
- Node.js (LTS version recommended)
- PHP 8+ with Composer
- MySQL or MariaDB

## Installation
Clone the repository and install dependencies:

```sh
git clone https://github.com/theo-georgewill/socialscheduler.git
cd socialscheduler
npm install
composer install
```

## Environment Setup
Create a `.env` file by copying the example:
```sh
cp .env.example .env
```
Generate the application key:
```sh
php artisan key:generate
```
Set up the database:
```sh
php artisan migrate --seed
```

## Running the Development Server
Start the backend Laravel server:
```sh
php artisan serve
```
Start the frontend Vue development server:
```sh
npm run dev
```

## Building for Production
Compile and minify frontend assets:
```sh
npm run build
```
Run database migrations and optimize Laravel:
```sh
php artisan migrate --force
php artisan config:cache
```

## Contribution
If you'd like to contribute to SocialScheduler, please fork the repository and submit a pull request.

## License
MIT License. See `LICENSE` for details.

---
🚀 Start automating your social media workflow with SocialScheduler!

