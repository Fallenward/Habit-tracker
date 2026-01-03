# Installation Guide

## Prerequisites

- Node.js 18+ and npm
- Laravel backend running on http://localhost:8000

## Steps

1. **Navigate to frontend directory:**
```bash
cd frontend
```

2. **Install dependencies:**
```bash
npm install
```

3. **Create .env file:**
```bash
# Windows PowerShell
echo "VITE_API_URL=http://localhost:8000/api" > .env

# Or manually create .env file with:
# VITE_API_URL=http://localhost:8000/api
```

4. **Start development server:**
```bash
npm run dev
```

5. **Open browser:**
Navigate to `http://localhost:3000`

## Troubleshooting

### CORS Errors
Make sure your Laravel backend has CORS configured to allow `http://localhost:3000`

### API Connection Issues
- Verify backend is running on `http://localhost:8000`
- Check `.env` file has correct `VITE_API_URL`
- Check browser console for errors

### Authentication Issues
- Make sure you're using the correct API endpoints
- Check that token is being stored in localStorage
- Verify Sanctum is properly configured

