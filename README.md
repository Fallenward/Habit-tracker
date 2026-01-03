# Habit Tracker

A full-stack habit tracking application with Laravel backend and React frontend.

## Features

- ✅ User Authentication (Register/Login)
- ✅ Create, Read, Update, Delete Habits
- ✅ Today's Checklist with Done/Undo
- ✅ Calendar View
- ✅ Statistics Dashboard
- ✅ Habit Logging System

## Backend (Laravel)

### Setup

1. Install dependencies:
```bash
composer install
```

2. Copy environment file:
```bash
cp .env.example .env
```

3. Generate application key:
```bash
php artisan key:generate
```

4. Run migrations:
```bash
php artisan migrate
```

5. Start server:
```bash
php artisan serve
```

The API will be available at `http://localhost:8000`

### API Endpoints

- `POST /api/register` - Register new user
- `POST /api/login` - Login user
- `POST /api/logout` - Logout user
- `GET /api/v1/user` - Get current user
- `GET /api/v1/habits` - List all habits
- `GET /api/v1/habits/today` - Get today's checklist
- `POST /api/v1/habits` - Create habit
- `GET /api/v1/habits/{id}` - Get habit
- `PUT /api/v1/habits/{id}` - Update habit
- `DELETE /api/v1/habits/{id}` - Delete habit
- `PUT /api/v1/logs/{date}` - Mark habit as done/undone
- `GET /api/v1/calendar?month=YYYY-MM` - Get calendar
- `GET /api/v1/calendar/{date}` - Get date details
- `GET /api/v1/stats?range=30d` - Get statistics

### Testing

Run tests:
```bash
php artisan test
```

## Frontend (React)

### Setup

1. Navigate to frontend directory:
```bash
cd frontend
```

2. Install dependencies:
```bash
npm install
```

3. Create `.env` file:
```bash
cp .env.example .env
```

4. Update `.env`:
```
VITE_API_URL=http://localhost:8000/api
```

5. Start development server:
```bash
npm run dev
```

The frontend will be available at `http://localhost:3000`

### Build for Production

```bash
npm run build
```

## Tech Stack

### Backend
- Laravel 12
- Laravel Sanctum (Authentication)
- SQLite/PostgreSQL
- Pest (Testing)

### Frontend
- React 18
- Vite
- React Router
- Axios
- Tailwind CSS

## Project Structure

```
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── V1/
│   │   │   │   ├── HabitController.php
│   │   │   │   ├── LogController.php
│   │   │   │   ├── CalendarController.php
│   │   │   │   └── StatsController.php
│   │   │   └── Auth/
│   │   └── Resources/
│   └── Models/
├── frontend/
│   ├── src/
│   │   ├── components/
│   │   ├── pages/
│   │   ├── contexts/
│   │   └── services/
│   └── public/
└── tests/
```

## License

MIT 
