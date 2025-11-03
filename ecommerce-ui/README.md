# E-Commerce UI

A modern React TypeScript application for the e-commerce platform with role-based interfaces for customers, businesses, and administrators.

## Features

- **Multi-user Authentication**: Support for customers, businesses, and admins
- **Responsive Design**: Mobile-first design with Tailwind CSS
- **Real-time Updates**: Redux Toolkit Query for efficient API state management
- **Role-based Access**: Protected routes and user-specific interfaces
- **Modern UI Components**: Built with Headless UI and Heroicons

## Tech Stack

- **Frontend**: React 18 + TypeScript
- **Styling**: Tailwind CSS + Headless UI
- **State Management**: Redux Toolkit + RTK Query
- **Routing**: React Router v6
- **Forms**: React Hook Form + Zod validation
- **Build Tool**: Vite

## Getting Started

### Prerequisites

- Node.js 18+ 
- npm or yarn
- Laravel API backend running on http://localhost:8000

### Installation

1. Clone the repository
2. Install dependencies:
   ```bash
   npm install
   ```

3. Copy environment variables:
   ```bash
   cp .env.example .env
   ```

4. Start the development server:
   ```bash
   npm run dev
   ```

5. Open http://localhost:3000 in your browser

## Available Scripts

- `npm run dev` - Start development server
- `npm run build` - Build for production
- `npm run preview` - Preview production build
- `npm run lint` - Run ESLint

## Project Structure

```
src/
├── components/          # Reusable UI components
├── pages/              # Page components by user type
├── store/              # Redux store and API services
├── types/              # TypeScript type definitions
├── hooks/              # Custom React hooks
└── utils/              # Utility functions
```

## User Types

### Customer
- Browse and search products
- Add items to cart and checkout
- View order history
- Manage profile

### Business
- Manage product listings
- Track sales and orders
- Business profile management
- Product approval status

### Admin
- Review and approve products
- Manage users and businesses
- Monitor platform analytics
- Category management

## API Integration

The application integrates with the Laravel API backend using RTK Query for:
- Authentication (multi-guard)
- Product management
- Order processing
- User management
- Real-time notifications

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Run tests and linting
5. Submit a pull request