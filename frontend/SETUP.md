# Frontend Setup Guide

## Quick Start

1. **Install dependencies:**
```bash
npm install
```

2. **Create environment file:**
```bash
# Create .env file in frontend directory
echo "VITE_API_URL=http://localhost:8000/api" > .env
```

3. **Start development server:**
```bash
npm run dev
```

4. **Open browser:**
Navigate to `http://localhost:3000`

## Environment Variables

Create a `.env` file in the `frontend` directory:

```
VITE_API_URL=http://localhost:8000/api
```

## Development

- Frontend runs on: `http://localhost:3000`
- Backend API should run on: `http://localhost:8000`

## Build for Production

```bash
npm run build
```

The built files will be in the `dist` directory.

