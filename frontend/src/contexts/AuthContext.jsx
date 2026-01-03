import { createContext, useContext, useState, useEffect } from 'react';
import { authAPI } from '../services/api';

const AuthContext = createContext(null);

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth must be used within AuthProvider');
  }
  return context;
};

export const AuthProvider = ({ children }) => {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const token = localStorage.getItem('token');
    if (token) {
      authAPI.getUser()
        .then((response) => {
          setUser(response.data);
        })
        .catch(() => {
          localStorage.removeItem('token');
          localStorage.removeItem('user');
        })
        .finally(() => {
          setLoading(false);
        });
    } else {
      setLoading(false);
    }
  }, []);

  const login = async (email, password) => {
    try {
      const response = await authAPI.login({ email, password });
      const token = response.data.token;

      if (token) {
        localStorage.setItem('token', token);
        const userResponse = await authAPI.getUser();
        setUser(userResponse.data);
        localStorage.setItem('user', JSON.stringify(userResponse.data));
        return { success: true };
      }

      return { success: false, error: 'Login failed' };
    } catch (error) {
      return {
        success: false,
        error: error.response?.data?.message || error.response?.data?.error || 'Login failed',
      };
    }
  };

  const register = async (name, email, password, password_confirmation) => {
    try {
      const response = await authAPI.register({
        name,
        email,
        password,
        password_confirmation,
      });

      const token = response.data.token;

      if (token) {
        localStorage.setItem('token', token);
        const user = response.data.user || response.data.data;
        if (user) {
          setUser(user);
          localStorage.setItem('user', JSON.stringify(user));
        } else {
          const userResponse = await authAPI.getUser();
          setUser(userResponse.data);
          localStorage.setItem('user', JSON.stringify(userResponse.data));
        }
        return { success: true };
      }

      return { success: false, error: 'Registration failed' };
    } catch (error) {
      const errorMessage = error.response?.data?.message ||
                          (error.response?.data?.errors ? JSON.stringify(error.response.data.errors) : null) ||
                          'Registration failed';
      return {
        success: false,
        error: errorMessage,
      };
    }
  };

  const logout = async () => {
    try {
      await authAPI.logout();
    } catch (error) {
      console.error('Logout error:', error);
    } finally {
      localStorage.removeItem('token');
      localStorage.removeItem('user');
      setUser(null);
    }
  };

  return (
    <AuthContext.Provider value={{ user, loading, login, register, logout }}>
      {children}
    </AuthContext.Provider>
  );
};

