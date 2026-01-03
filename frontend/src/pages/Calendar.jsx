import { useState, useEffect } from 'react';
import { calendarAPI } from '../services/api';

export default function Calendar() {
  const [calendar, setCalendar] = useState([]);
  const [selectedDate, setSelectedDate] = useState(null);
  const [dateDetails, setDateDetails] = useState(null);
  const [currentMonth, setCurrentMonth] = useState(
    new Date().toISOString().slice(0, 7)
  );
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    loadCalendar();
  }, [currentMonth]);

  useEffect(() => {
    if (selectedDate) {
      loadDateDetails(selectedDate);
    }
  }, [selectedDate]);

  const loadCalendar = async () => {
    try {
      setLoading(true);
      const response = await calendarAPI.getMonth(currentMonth);
      const data = response.data.data || response.data;
      setCalendar(data.calendar || data || []);
    } catch (err) {
      console.error('Failed to load calendar:', err);
    } finally {
      setLoading(false);
    }
  };

  const loadDateDetails = async (date) => {
    try {
      const response = await calendarAPI.getDate(date);
      const data = response.data.data || response.data;
      setDateDetails(data);
    } catch (err) {
      console.error('Failed to load date details:', err);
    }
  };

  const changeMonth = (direction) => {
    const date = new Date(currentMonth + '-01');
    date.setMonth(date.getMonth() + direction);
    setCurrentMonth(date.toISOString().slice(0, 7));
  };

  const formatMonth = (monthString) => {
    const date = new Date(monthString + '-01');
    return date.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
  };

  const weekdays = ['M', 'T', 'W', 'T', 'F', 'S', 'S'];

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
        <div className="flex items-center justify-between mb-4">
          <button
            onClick={() => changeMonth(-1)}
            className="p-2 hover:bg-gray-100 rounded"
          >
            ‹
          </button>
          <h2 className="text-2xl font-bold text-gray-900">
            {formatMonth(currentMonth)}
          </h2>
          <button
            onClick={() => changeMonth(1)}
            className="p-2 hover:bg-gray-100 rounded"
          >
            ›
          </button>
        </div>
      </div>

      <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-4">
        <div className="grid grid-cols-7 gap-2 mb-2">
          {weekdays.map((day, idx) => (
            <div key={idx} className="text-center text-sm font-medium text-gray-500">
              {day}
            </div>
          ))}
        </div>
        <div className="grid grid-cols-7 gap-2">
          {calendar.map((day, idx) => (
            <button
              key={idx}
              onClick={() => setSelectedDate(day.date)}
              className={`p-2 rounded-lg text-center ${
                day.completed_habits > 0
                  ? 'bg-gray-900 text-white'
                  : 'bg-gray-50 text-gray-700 hover:bg-gray-100'
              } ${
                selectedDate === day.date ? 'ring-2 ring-gray-900' : ''
              }`}
            >
              <div className="text-sm font-medium">{day.day}</div>
              {day.completed_habits > 0 && (
                <div className="text-xs mt-1">✓</div>
              )}
            </button>
          ))}
        </div>
      </div>

      {dateDetails && selectedDate && (
        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
          <h3 className="font-semibold text-gray-900 mb-2">
            {new Date(selectedDate).toLocaleDateString('en-US', {
              weekday: 'long',
              month: 'long',
              day: 'numeric',
            })}
          </h3>
          <p className="text-sm text-gray-500 mb-4">
            {dateDetails.completed_count} of {dateDetails.total_habits} completed
          </p>
          <div className="space-y-2">
            {dateDetails.habits.map((habit) => (
              <div
                key={habit.id}
                className="flex items-center gap-2 text-sm"
              >
                <span className={habit.completed ? 'text-gray-900' : 'text-gray-400'}>
                  {habit.completed ? '✓' : '○'}
                </span>
                <span className={habit.completed ? 'text-gray-900' : 'text-gray-500'}>
                  {habit.title}
                </span>
              </div>
            ))}
          </div>
        </div>
      )}
    </div>
  );
}

