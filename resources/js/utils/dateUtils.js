/**
 * Date utility functions for mood tracker calendar views
 */

/**
 * Get the start of the week (Monday) for a given date
 * @param {Date} date - The date to find the week start for
 * @returns {Date} - The Monday of that week
 */
export function getWeekStart(date) {
    const d = new Date(date);
    const day = d.getDay(); // 0 = Sunday, 1 = Monday, etc.
    const diff = day === 0 ? -6 : 1 - day; // If Sunday, go back 6 days; otherwise go back to Monday
    d.setDate(d.getDate() + diff);
    d.setHours(0, 0, 0, 0);
    return d;
}

/**
 * Get the end of the week (Sunday) for a given date
 * @param {Date} date - The date to find the week end for
 * @returns {Date} - The Sunday of that week
 */
export function getWeekEnd(date) {
    const d = new Date(date);
    const day = d.getDay(); // 0 = Sunday
    const diff = day === 0 ? 0 : 7 - day; // If Sunday, it's the end; otherwise calculate days to Sunday
    d.setDate(d.getDate() + diff);
    d.setHours(23, 59, 59, 999);
    return d;
}

/**
 * Get an array of 7 dates for a given week
 * @param {Date} weekStart - The start date of the week (Monday)
 * @returns {Date[]} - Array of 7 dates from Monday to Sunday
 */
export function getDaysInWeek(weekStart) {
    const days = [];
    for (let i = 0; i < 7; i++) {
        const date = new Date(weekStart);
        date.setDate(date.getDate() + i);
        days.push(date);
    }
    return days;
}

/**
 * Get a 2D array of dates organized by weeks for a given month
 * Each week starts on Monday and ends on Sunday
 * Includes days from previous/next month to fill complete weeks
 * @param {number} year - The year
 * @param {number} month - The month (0-11)
 * @returns {Date[][]} - Array of weeks, each week is an array of 7 dates
 */
export function getDaysInMonth(year, month) {
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);

    const weeks = [];
    let currentWeekStart = getWeekStart(firstDay);

    // Keep adding weeks until we've passed the last day of the month
    while (currentWeekStart <= lastDay || weeks.length === 0) {
        const week = getDaysInWeek(currentWeekStart);
        weeks.push(week);

        // Check if this week contains any days from the target month
        const hasMonthDays = week.some(date => date.getMonth() === month);

        // Move to next week
        currentWeekStart = new Date(currentWeekStart);
        currentWeekStart.setDate(currentWeekStart.getDate() + 7);

        // Stop if we've gone past the month and the last week had days from the month
        if (currentWeekStart > lastDay && hasMonthDays) {
            break;
        }
    }

    return weeks;
}

/**
 * Check if two dates are the same day
 * @param {Date} date1 - First date
 * @param {Date} date2 - Second date
 * @returns {boolean} - True if same day
 */
export function isSameDay(date1, date2) {
    if (!date1 || !date2) return false;
    return date1.toDateString() === date2.toDateString();
}

/**
 * Check if a date is today
 * @param {Date} date - The date to check
 * @returns {boolean} - True if the date is today
 */
export function isToday(date) {
    return isSameDay(date, new Date());
}

/**
 * Format a week range for display
 * @param {Date} weekStart - The start of the week
 * @returns {string} - Formatted string like "Mar 9 - Mar 15, 2025"
 */
export function formatWeekRange(weekStart) {
    const weekEnd = new Date(weekStart);
    weekEnd.setDate(weekEnd.getDate() + 6);

    const startMonth = weekStart.toLocaleDateString('es-ES', { month: 'short' });
    const startDay = weekStart.getDate();
    const endMonth = weekEnd.toLocaleDateString('es-ES', { month: 'short' });
    const endDay = weekEnd.getDate();
    const year = weekEnd.getFullYear();

    if (startMonth === endMonth) {
        return `${startMonth} ${startDay} - ${endDay}, ${year}`;
    } else {
        return `${startMonth} ${startDay} - ${endMonth} ${endDay}, ${year}`;
    }
}

/**
 * Get month name
 * @param {number} month - Month number (0-11)
 * @returns {string} - Month name
 */
export function getMonthName(month) {
    const monthNames = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'
    ];
    return monthNames[month];
}

/**
 * Get short day names
 * @returns {string[]} - Array of short day names
 */
export function getDayNames() {
    return ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
}

/**
 * Check if a year is a leap year
 * @param {number} year - The year to check
 * @returns {boolean} - True if leap year
 */
export function isLeapYear(year) {
    return (year % 4 === 0 && year % 100 !== 0) || (year % 400 === 0);
}

/**
 * Get number of days in a month
 * @param {number} year - The year
 * @param {number} month - The month (0-11)
 * @returns {number} - Number of days in the month
 */
export function getDaysInMonthCount(year, month) {
    return new Date(year, month + 1, 0).getDate();
}
