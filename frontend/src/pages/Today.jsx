import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { habitsAPI, logsAPI } from '../services/api';

export default function Today() {
  const [habits, setHabits] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const navigate = useNavigate();

  const today = new Date().toISOString().split('T')[0];

  useEffect(() => {
    loadTodayHabits();
  }, []);

  const loadTodayHabits = async () => {
    try {
      setLoading(true);
      const response = await habitsAPI.getToday();
      const data = response.data.data || response.data;
      setHabits(data.habits || data || []);
    } catch (err) {
      setError('Failed to load habits');
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  const toggleHabit = async (habitId, completed) => {
    try {
      await logsAPI.update(today, {
        habit_id: habitId,
        completed: !completed,
      });
      loadTodayHabits();
    } catch (err) {
      console.error('Failed to update habit:', err);
    }
  };

  const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
      weekday: 'short',
      month: 'short',
      day: 'numeric',
    });
  };

  if (loading) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="text-lg text-gray-500">Loading...</div>
      </div>
    );
  }

  return (
    <div className="pb-24">
      <div className="mb-6">
        <h2 className="text-2xl font-bold text-gray-900">Today</h2>
        <p className="text-gray-600 mt-1">{formatDate(today)}</p>
      </div>

      {error && (
        <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
          {error}
        </div>
      )}

      {habits.length === 0 ? (
        <div className="text-center py-12">
          <p className="text-gray-500 mb-4">No habits for today</p>
          <button
            onClick={() => navigate('/habits/add')}
            className="px-4 py-2 bg-gray-900 text-white rounded-lg hover:bg-gray-800"
          >
            Add Your First Habit
          </button>
        </div>
      ) : (
        <div className="space-y-4">
          {habits.map((habit) => (
            <div
              key={habit.id}
              className="bg-white rounded-lg shadow-sm border border-gray-200 p-4 flex items-center justify-between"
            >
              <div className="flex items-center gap-4 flex-1">
                <button
                  onClick={() => toggleHabit(habit.id, habit.completed)}
                  className={`w-6 h-6 rounded-full border-2 flex items-center justify-center ${
                    habit.completed
                      ? 'bg-gray-900 border-gray-900'
                      : 'border-gray-300'
                  }`}
                >
                  {habit.completed && (
                    <span className="text-white text-xs">✓</span>
                  )}
                </button>
                <div className="flex-1">
                  <h3 className="font-semibold text-gray-900">{habit.title || habit.name}</h3>
                  <p className="text-sm text-gray-500">{habit.description}</p>
                </div>
              </div>
              <div className="flex gap-2">
                <button
                  onClick={() => navigate(`/habits/${habit.id}/edit`)}
                  className="px-3 py-2 text-sm text-gray-600 hover:text-gray-900"
                >
                  Edit
                </button>
                <button
                  onClick={() => toggleHabit(habit.id, habit.completed)}
                  className={`px-4 py-2 rounded-lg text-sm font-medium ${
                    habit.completed
                      ? 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                      : 'bg-gray-900 text-white hover:bg-gray-800'
                  }`}
                >
                  {habit.completed ? 'Undo' : 'Done'}
                </button>
              </div>
            </div>
          ))}
        </div>
      )}

      <div className="mt-6 text-center text-sm text-gray-500">
        {habits.filter((h) => h.completed).length} of {habits.length} completed
      </div>
    </div>
  );
}

