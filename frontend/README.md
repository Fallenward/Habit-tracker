# Habit Tracker Frontend

Frontend application for Habit Tracker built with React and Vite.

## Setup

1. Install dependencies:
```bash
npm install
```

2. Create `.env` file:
```bash
cp .env.example .env
```

3. Update `.env` with your API URL:
```
VITE_API_URL=http://localhost:8000/api
```

4. Start development server:
```bash
npm run dev
```

The app will be available at `http://localhost:3000`

## Features

- ✅ User Authentication (Login/Register)
- ✅ Today's Checklist
- ✅ Calendar View
- ✅ Statistics Dashboard
- ✅ Add/Edit/Delete Habits
- ✅ Mark Habits as Done/Undo

## API Connection

The frontend connects to the Laravel API backend. Make sure:
- Laravel backend is running on `http://localhost:8000`
- CORS is properly configured
- Sanctum authentication is set up

