import { useState, useEffect } from 'react';
import { statsAPI } from '../services/api';

export default function Stats() {
  const [stats, setStats] = useState(null);
  const [range, setRange] = useState('30d');
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    loadStats();
  }, [range]);

  const loadStats = async () => {
    try {
      setLoading(true);
      const response = await statsAPI.get(range);
      setStats(response.data);
    } catch (err) {
      console.error('Failed to load stats:', err);
    } finally {
      setLoading(false);
    }
  };

  if (loading || !stats) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="text-lg text-gray-500">Loading...</div>
      </div>
    );
  }

  const completionRate = stats.completion.rate || 0;
  const maxBarHeight = 130;

  return (
    <div className="pb-24 space-y-6">
      <div className="flex items-center justify-between mb-6">
        <h2 className="text-2xl font-bold text-gray-900">Stats</h2>
        <select
          value={range}
          onChange={(e) => setRange(e.target.value)}
          className="px-3 py-2 border border-gray-300 rounded-lg text-sm"
        >
          <option value="7d">Last 7 days</option>
          <option value="30d">Last 30 days</option>
          <option value="90d">Last 90 days</option>
        </select>
      </div>

      {/* Current Streak & Completion */}
      <div className="grid grid-cols-2 gap-4">
        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
          <p className="text-sm text-gray-500 mb-1">Current Streak</p>
          <p className="text-3xl font-bold text-gray-900">{stats.current_streak}</p>
          <p className="text-xs text-gray-500 mt-1">days</p>
        </div>
        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
          <p className="text-sm text-gray-500 mb-1">Completion</p>
          <p className="text-3xl font-bold text-gray-900">
            {stats.completion.completed} / {stats.completion.total}
          </p>
          <p className="text-xs text-gray-500 mt-1">
            {completionRate.toFixed(1)}%
          </p>
        </div>
      </div>

      {/* Weekly Overview */}
      <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <h3 className="font-semibold text-gray-900 mb-4">Weekly Overview</h3>
        <div className="flex items-end justify-between gap-2 h-40">
          {stats.weekly_overview.map((day, idx) => {
            const height = day.total_habits > 0
              ? (day.completed_count / day.total_habits) * maxBarHeight
              : 0;
            return (
              <div key={idx} className="flex-1 flex flex-col items-center">
                <div
                  className="w-full bg-gray-900 rounded-t"
                  style={{ height: `${height}px` }}
                />
                <div className="mt-2 text-xs text-gray-500">{day.day}</div>
              </div>
            );
          })}
        </div>
      </div>

      {/* Top Habits */}
      <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <h3 className="font-semibold text-gray-900 mb-4">Top Habits</h3>
        {stats.top_habits.length === 0 ? (
          <p className="text-sm text-gray-500">No habits yet</p>
        ) : (
          <div className="space-y-3">
            {stats.top_habits.map((habit, idx) => (
              <div key={habit.id}>
                <div className="flex items-center justify-between mb-1">
                  <span className="text-sm font-medium text-gray-900">
                    {habit.title}
                  </span>
                  <span className="text-sm text-gray-500">
                    {habit.completion_rate.toFixed(1)}%
                  </span>
                </div>
                <div className="w-full bg-gray-200 rounded-full h-2">
                  <div
                    className="bg-gray-900 h-2 rounded-full"
                    style={{ width: `${habit.completion_rate}%` }}
                  />
                </div>
              </div>
            ))}
          </div>
        )}
      </div>
    </div>
  );
}

