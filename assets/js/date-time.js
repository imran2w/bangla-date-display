class BanglaDateTime {
    constructor() {
        this.digits = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
        this.weekdays = ['রবিবার', 'সোমবার', 'মঙ্গলবার', 'বুধবার', 'বৃহস্পতিবার', 'শুক্রবার', 'শনিবার'];
        this.months = ['জানুয়ারি', 'ফেব্রুয়ারি', 'মার্চ', 'এপ্রিল', 'মে', 'জুন', 'জুলাই', 'আগস্ট', 'সেপ্টেম্বর', 'অক্টোবর', 'নভেম্বর', 'ডিসেম্বর'];
        this.hijriMonths = ['মুহাররম', 'সফর', 'রবিউল আউয়াল', 'রবিউস সানি', 'জমাদিউল আউয়াল', 'জমাদিউস সানি', 'রজব', 'শাবান', 'রমজান', 'শাওয়াল', 'জিলকদ', 'জিলহজ'];
        this.banglaMonths = ['বৈশাখ', 'জ্যৈষ্ঠ', 'আষাঢ়', 'শ্রাবণ', 'ভাদ্র', 'আশ্বিন', 'কার্তিক', 'অগ্রহায়ণ', 'পৌষ', 'মাঘ', 'ফাল্গুন', 'চৈত্র'];
    }

    // Initialization
    init() {
        const timeElement = document.querySelectorAll('.bangla-time');
        const dayElement = document.querySelectorAll('.bangla-day');
        const gDateElement = document.querySelectorAll('.bangla-gregorian-date');
        const bDateElement = document.querySelectorAll('.bangla-date');
        const seasonElement = document.querySelectorAll('.bangla-season');
        const hijriDateElement = document.querySelectorAll('.bangla-hijri-date');

        if (timeElement) {
            timeElement.forEach(el => el.textContent = this.getTime());
        }
        if (dayElement) {
            dayElement.forEach(el => el.textContent = this.getDay());
        }
        if (gDateElement) {
            gDateElement.forEach(el => {
                const separator = el.dataset.separator || ', ';
                const ordSuffix = (el.dataset.ordSuffix || '1') === '1' ? true : false;
                const lastWord = (el.dataset.lastWord || '1') === '1' ? true : false;
                el.textContent = this.getDate(ordSuffix, separator, lastWord);
            });
        }
        if (bDateElement) {
            bDateElement.forEach(el => {
                const calendar = el.dataset.calendar || 'BD';
                const separator = el.dataset.separator || ', ';
                const ordSuffix = (el.dataset.ordSuffix || '1') === '1' ? true : false;
                const lastWord = (el.dataset.lastWord || '1') === '1' ? true : false;
                el.textContent = this.getBanglaDate(calendar, ordSuffix, separator, lastWord);
            });
        }
        if (seasonElement) {
            seasonElement.forEach(el => el.textContent = this.getBanglaSeason());
        }
        if (hijriDateElement) {
            hijriDateElement.forEach(el => {
                const calendar = el.dataset.calendar || 'umalqura';
                const separator = el.dataset.separator || ', ';
                const ordSuffix = (el.dataset.ordSuffix || '1') === '1' ? true : false;
                const lastWord = (el.dataset.lastWord || '1') === '1' ? true : false;
                let adjustDays = parseInt(el.dataset.adjust || '0', 10);
                if (!Number.isFinite(adjustDays)) {
                    adjustDays = 0;
                }

                // Backward compatibility: old values were stored as hours (-72..72).
                if (Math.abs(adjustDays) > 3 && adjustDays % 24 === 0) {
                    adjustDays = adjustDays / 24;
                }

                el.textContent = this.getHijriDate(calendar, ordSuffix, separator, lastWord, adjustDays);
            });
        }
    }

    // Helpers
    toBanglaNumber(num) {
        return String(num).split('').map(d => this.digits[+d]).join('');
    }

    getTimePeriod(hour) {
        if (hour >= 0 && hour < 4)   return 'রাত';
        if (hour >= 4 && hour < 6)   return 'ভোর';
        if (hour >= 6 && hour < 12)  return 'সকাল';
        if (hour >= 12 && hour < 15) return 'দুপুর';
        if (hour >= 15 && hour < 18) return 'বিকাল';
        if (hour >= 18 && hour < 20) return 'সন্ধ্যা';
        return 'রাত';
    }

    // Main methods
    getTime() {
        const now = new Date();
        const hour = now.getHours();
        const minute = now.getMinutes();
        const period = this.getTimePeriod(hour);
        const displayHour = hour % 12 || 12;
        const banglaHour = this.toBanglaNumber(displayHour);
        const banglaMinute = this.toBanglaNumber(String(minute).padStart(2, '0'));

        return `${period} ${banglaHour}:${banglaMinute}`;
    }

    getDay() {
        const now = new Date();
        const day = this.weekdays[now.getDay()];
        return day;
    }

    getDayOrdinal(day) {
        if (day === 1)  return 'লা';
        if (day === 2 || day === 3) return 'রা';
        if (day === 4)  return 'ঠা';
        if (day <= 18)  return 'ই';
        return 'শে';
    }

    getDate(showOrdinal = true, separator = ', ', includeEra = true) {
        const now = new Date();
        const day = now.getDate();
        const month = this.months[now.getMonth()];
        const year = this.toBanglaNumber(now.getFullYear());
        const banglaDay = this.toBanglaNumber(day);
        const ordinal = showOrdinal ? this.getDayOrdinal(day) : '';
        const era = includeEra ? ' খ্রিস্টাব্দ' : '';
        return `${banglaDay}${ordinal} ${month}${separator}${year}${era}`;
    }

    _getBanglaMonthIndex(calendar = 'BD') {
        const now = new Date();
        const gYear = now.getFullYear();
        const isGLeap = y => (y % 4 === 0 && y % 100 !== 0) || y % 400 === 0;
        const newYearDay = c => c === 'IN' ? (isGLeap(gYear) ? 14 : 15) : 14;
        const prevNewYearDay = c => c === 'IN' ? (isGLeap(gYear - 1) ? 14 : 15) : 14;

        const baishakhStart = new Date(gYear, 3, newYearDay(calendar));
        const banglaYearStart = now >= baishakhStart
            ? baishakhStart
            : new Date(gYear - 1, 3, prevNewYearDay(calendar));

        const dayOfYear = Math.floor((now - banglaYearStart) / 86400000);
        const falgunGYear = banglaYearStart.getFullYear() + 1;
        const monthDays = [31, 31, 31, 31, 31, 30, 30, 30, 30, 30, isGLeap(falgunGYear) ? 31 : 30, 30];

        let monthIndex = 0;
        let remaining = dayOfYear;
        for (let i = 0; i < 12; i++) {
            if (remaining < monthDays[i]) { monthIndex = i; break; }
            remaining -= monthDays[i];
        }
        return { monthIndex, remaining };
    }

    getBanglaDate(calendar = 'BD', showOrdinal = true, separator = ', ', includeEra = true) {
        const now = new Date();
        const gYear = now.getFullYear();
        const isGLeap = y => (y % 4 === 0 && y % 100 !== 0) || y % 400 === 0;
        const newYearDay = c => c === 'IN' ? (isGLeap(gYear) ? 14 : 15) : 14;
        const prevNewYearDay = c => c === 'IN' ? (isGLeap(gYear - 1) ? 14 : 15) : 14;

        const baishakhStart = new Date(gYear, 3, newYearDay(calendar));
        const banglaYear = now >= baishakhStart ? gYear - 593 : gYear - 594;

        const { monthIndex, remaining } = this._getBanglaMonthIndex(calendar);
        const banglaDay = remaining + 1;
        const banglaMonth = this.banglaMonths[monthIndex];
        const ordinal = showOrdinal ? this.getDayOrdinal(banglaDay) : '';
        const era = includeEra ? ' বঙ্গাব্দ' : '';
        return `${this.toBanglaNumber(banglaDay)}${ordinal} ${banglaMonth}${separator}${this.toBanglaNumber(banglaYear)}${era}`;
    }

    getBanglaSeason(calendar = 'BD') {
        const { monthIndex } = this._getBanglaMonthIndex(calendar);
        if (monthIndex <= 1)  return 'গ্রীষ্মকাল';  // বৈশাখ–জ্যৈষ্ঠ
        if (monthIndex <= 3)  return 'বর্ষাকাল';    // আষাঢ়–শ্রাবণ
        if (monthIndex <= 5)  return 'শরৎকাল';     // ভাদ্র–আশ্বিন
        if (monthIndex <= 7)  return 'হেমন্তকাল';   // কার্তিক–অগ্রহায়ণ
        if (monthIndex <= 9)  return 'শীতকাল';     // পৌষ–মাঘ
        return 'বসন্তকাল';                          // ফাল্গুন–চৈত্র
    }

    getHijriDate(calendar = 'umalqura', showOrdinal = true, separator = ', ', includeEra = true, dayOffset = 0) {
        const now = new Date( Date.now() + dayOffset * 86400000 );
        const parts = new Intl.DateTimeFormat(`en-u-ca-islamic-${calendar}`, {
            day: 'numeric',
            month: 'numeric',
            year: 'numeric'
        }).formatToParts(now);

        const get = type => parseInt(parts.find(p => p.type === type).value);
        const day = get('day');
        const monthIndex = get('month') - 1;
        const year = get('year');

        const banglaDay = this.toBanglaNumber(day);
        const ordinal = showOrdinal ? this.getDayOrdinal(day) : '';
        const banglaMonth = this.hijriMonths[monthIndex];
        const era = includeEra ? ' হিজরি' : '';
        return `${banglaDay}${ordinal} ${banglaMonth}${separator}${this.toBanglaNumber(year)}${era}`;
    }
}

// Initialize on DOMContentLoaded
document.addEventListener('DOMContentLoaded', () => {
    const banglaDateTime = new BanglaDateTime();
    banglaDateTime.init();
});
