import { useState, useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { habitsAPI } from '../services/api';

export default function EditHabit() {
  const { id } = useParams();
  const [title, setTitle] = useState('');
  const [description, setDescription] = useState('');
  const [rrule, setRrule] = useState('FREQ=DAILY');
  const [time, setTime] = useState('08:00');
  const [loading, setLoading] = useState(false);
  const [loadingData, setLoadingData] = useState(true);
  const [error, setError] = useState('');
  const navigate = useNavigate();

  useEffect(() => {
    loadHabit();
  }, [id]);

  const loadHabit = async () => {
    try {
      setLoadingData(true);
      const response = await habitsAPI.getOne(id);
      const habit = response.data.data || response.data;
      setTitle(habit.name || habit.title || '');
      setDescription(habit.description || '');
      if (habit.schedule) {
        setRrule(habit.schedule.rrule || 'FREQ=DAILY');
        setTime(habit.schedule.time || '08:00');
      }
    } catch (err) {
      setError('Failed to load habit');
      console.error(err);
    } finally {
      setLoadingData(false);
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setLoading(true);

    try {
      await habitsAPI.update(id, {
        title,
        description,
        schedule: {
          rrule,
          time,
        },
      });
      navigate('/');
    } catch (err) {
      setError(err.response?.data?.message || 'Failed to update habit');
    } finally {
      setLoading(false);
    }
  };

  const handleDelete = async () => {
    if (!window.confirm('Are you sure you want to delete this habit?')) {
      return;
    }

    try {
      await habitsAPI.delete(id);
      navigate('/');
    } catch (err) {
      setError(err.response?.data?.message || 'Failed to delete habit');
    }
  };

  if (loadingData) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="text-lg text-gray-500">Loading...</div>
      </div>
    );
  }

  return (
    <div className="pb-24">
      <div className="mb-6">
        <h2 className="text-2xl font-bold text-gray-900">Edit Habit</h2>
      </div>

      <form onSubmit={handleSubmit} className="space-y-6">
        {error && (
          <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
            {error}
          </div>
        )}

        <div>
          <label htmlFor="title" className="block text-sm font-medium text-gray-700 mb-2">
            Title
          </label>
          <input
            id="title"
            type="text"
            required
            value={title}
            onChange={(e) => setTitle(e.target.value)}
            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500"
          />
        </div>

        <div>
          <label htmlFor="description" className="block text-sm font-medium text-gray-700 mb-2">
            Description
          </label>
          <textarea
            id="description"
            required
            value={description}
            onChange={(e) => setDescription(e.target.value)}
            rows={3}
            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500"
          />
        </div>

        <div>
          <label htmlFor="rrule" className="block text-sm font-medium text-gray-700 mb-2">
            Schedule (RRULE)
          </label>
          <select
            id="rrule"
            value={rrule}
            onChange={(e) => setRrule(e.target.value)}
            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500"
          >
            <option value="FREQ=DAILY">Daily</option>
            <option value="FREQ=WEEKLY">Weekly</option>
          </select>
        </div>

        <div>
          <label htmlFor="time" className="block text-sm font-medium text-gray-700 mb-2">
            Time
          </label>
          <input
            id="time"
            type="time"
            required
            value={time}
            onChange={(e) => setTime(e.target.value)}
            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500"
          />
        </div>

        <div className="flex gap-4">
          <button
            type="submit"
            disabled={loading}
            className="flex-1 bg-gray-900 text-white py-2 px-4 rounded-lg hover:bg-gray-800 disabled:opacity-50"
          >
            {loading ? 'Saving...' : 'Update Habit'}
          </button>
          <button
            type="button"
            onClick={() => navigate('/')}
            className="flex-1 bg-gray-100 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-200"
          >
            Cancel
          </button>
        </div>

        <button
          type="button"
          onClick={handleDelete}
          className="w-full bg-red-50 text-red-700 py-2 px-4 rounded-lg hover:bg-red-100"
        >
          Delete Habit
        </button>
      </form>
    </div>
  );
}

