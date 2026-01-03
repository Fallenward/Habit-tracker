import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { habitsAPI } from '../services/api';

export default function AddHabit() {
  const [title, setTitle] = useState('');
  const [description, setDescription] = useState('');
  const [rrule, setRrule] = useState('FREQ=DAILY');
  const [time, setTime] = useState('08:00');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const navigate = useNavigate();

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setLoading(true);

    try {
      await habitsAPI.create({
        title,
        description,
        schedule: {
          rrule,
          time,
        },
      });
      navigate('/');
    } catch (err) {
      setError(err.response?.data?.message || 'Failed to create habit');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="pb-24">
      <div className="mb-6">
        <h2 className="text-2xl font-bold text-gray-900">Add Habit</h2>
        <p className="text-gray-600 mt-1">Create something you can track daily</p>
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
            placeholder="e.g., Read 20 minutes"
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
            placeholder="Describe your habit"
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
            {loading ? 'Saving...' : 'Save Habit'}
          </button>
          <button
            type="button"
            onClick={() => navigate('/')}
            className="flex-1 bg-gray-100 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-200"
          >
            Cancel
          </button>
        </div>
      </form>
    </div>
  );
}

