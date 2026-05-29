## About
Open chat backend is a set of real-time backend APIs for a communication platform 'Open chat' supporting instant messaging and video calling.

## Features
- Real-time chat: Messaging & information exchange via Pusher
- Video calling: Signaling for WebRTC based video calls
- Secure Auth: Laravel Passport with httponly token
- Data Management: MySql

## Prerequisites
- PHP: 8.1+
- Composer
- Nodejs & NPM
- Pusher Account

## Installation
1. Clone the repository
```git clone https://github.com/progssp/open_chat_backend.git
cd open_chat_backend```
   
2. Install dependencies
```composer install```

3. Setup environment
```cp .env.example .env
php artisan key:generate```
   
4. Configure database
```DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db_name
DB_USERNAME=db_user
DB_PASSWORD=db_password```   

5. Configure Pusher and Broadcasting
```BROADCAST_DRIVER=pusher
PUSHER_APP_ID=pusher_app_id
PUSHER_APP_KEY=pusher_app_key
PUSHER_APP_SECRET=pusher_app_secret
PUSHER_HOST=pusher_app_host
PUSHER_PORT=pusher_app_port
PUSHER_SCHEME=pusher_app_scheme
PUSHER_APP_CLUSTER=pusher_app_cluster```

6. Setup Laravel Passport
```php artisan migrate
php artisan passport:install
php artisan passport:client --personal --no-interaction```

## Technical Setup
- The BroadcastServiceProvider must be uncommented in config/app.php to enable private channels.

## Usage
- Start local devlopment server
`php artisan serve`
