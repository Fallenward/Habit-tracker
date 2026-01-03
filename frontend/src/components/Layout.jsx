import { Outlet, useNavigate, useLocation } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';

export default function Layout() {
  const { user, logout } = useAuth();
  const navigate = useNavigate();
  const location = useLocation();

  const handleLogout = async () => {
    await logout();
    navigate('/login');
  };

  const navItems = [
    { path: '/', label: 'Today', icon: '📅' },
    { path: '/calendar', label: 'Calendar', icon: '📆' },
    { path: '/stats', label: 'Stats', icon: '📊' },
  ];

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Header */}
      <header className="bg-white shadow-sm border-b">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex justify-between items-center h-16">
            <h1 className="text-2xl font-bold text-gray-900">Habit Tracker</h1>
            <div className="flex items-center gap-4">
              <span className="text-sm text-gray-600">{user?.name || user?.email}</span>
              <button
                onClick={handleLogout}
                className="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900"
              >
                Logout
              </button>
            </div>
          </div>
        </div>
      </header>

      {/* Main Content */}
      <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <Outlet />
      </main>

      {/* Bottom Navigation */}
      <nav className="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200">
        <div className="max-w-7xl mx-auto px-4">
          <div className="flex justify-around">
            {navItems.map((item) => (
              <button
                key={item.path}
                onClick={() => navigate(item.path)}
                className={`flex-1 py-4 text-center ${
                  location.pathname === item.path
                    ? 'text-gray-900 font-semibold border-b-2 border-gray-900'
                    : 'text-gray-500 hover:text-gray-700'
                }`}
              >
                <div className="text-2xl mb-1">{item.icon}</div>
                <div className="text-xs">{item.label}</div>
              </button>
            ))}
          </div>
        </div>
      </nav>

      {/* Add Habit Button */}
      <button
        onClick={() => navigate('/habits/add')}
        className="fixed bottom-20 right-6 bg-gray-900 text-white rounded-full w-14 h-14 flex items-center justify-center shadow-lg hover:bg-gray-800 transition-colors"
      >
        <span className="text-2xl">+</span>
      </button>
    </div>
  );
}

