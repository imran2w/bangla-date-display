class CalendarWidget {
    #ajaxConfig = null;

    static LOCALES = {
        en: {
            label:     'EN',
            months:    ['January','February','March','April','May','June',
                        'July','August','September','October','November','December'],
            days:      ['Su','Mo','Tu','We','Th','Fr','Sa'],
            toNumeral: (n) => String(n),
        },
        bn: {
            label:     'বাং',
            months:    ['জানুয়ারি','ফেব্রুয়ারি','মার্চ','এপ্রিল','মে','জুন',
                        'জুলাই','আগস্ট','সেপ্টেম্বর','অক্টোবর','নভেম্বর','ডিসেম্বর'],
            days:      ['রবি','সোম','মঙ্গ','বুধ','বৃহঃ','শুক্র','শনি'],
            toNumeral: (n) => String(n).replace(/\d/g, d => '০১২৩৪৫৬৭৮৯'[d]),
        },
    };

    constructor(options = {}) {
        this.root         = options.root;
        this.today        = new Date();
        this.currentYear  = this.today.getFullYear();
        this.currentMonth = this.today.getMonth(); // 0-indexed
        this.locale       = options.locale ?? 'en';
        this.startYear    = Number.isInteger(options.startYear) ? options.startYear : this.today.getFullYear() - 10;
        this.primaryColor = options.primaryColor ?? '#2271b1';
        this.#ajaxConfig  = options.ajaxConfig ?? window.calWidgetConfig ?? null;
        this.loadingCount = 0;

        if (!this.root) {
            throw new Error('Calendar widget root element is required.');
        }

        if (this.startYear > this.currentYear) {
            this.startYear = this.currentYear;
        }
    }

    get lang() {
        return CalendarWidget.LOCALES[this.locale];
    }

    /* ── Populate dropdowns ─────────────────────────── */
    populateDropdowns() {
        const monthSel = this.root.querySelector('.cal-month');
        const yearSel  = this.root.querySelector('.cal-year');

        monthSel.innerHTML = '';
        this.lang.months.forEach((name, i) => {
            const opt = document.createElement('option');
            opt.value = i;
            opt.textContent = name;
            if (i === this.currentMonth) { opt.selected = true; }
            monthSel.appendChild(opt);
        });

        yearSel.innerHTML = '';
        const endYear = this.today.getFullYear();
        for (let y = this.startYear; y <= endYear; y++) {
            const opt = document.createElement('option');
            opt.value = y;
            opt.textContent = this.lang.toNumeral(y);
            if (y === this.currentYear) { opt.selected = true; }
            yearSel.appendChild(opt);
        }
    }

    /* ── Render day headers ─────────────────────────── */
    renderDayHeaders() {
        this.root.querySelectorAll('.cal-table thead th').forEach((th, i) => {
            th.textContent = this.lang.days[i];
        });
    }

    /* ── Render calendar grid ───────────────────────── */
    render(year = this.currentYear, month = this.currentMonth) {
        this.root.querySelector('.cal-title').textContent =
            `${this.lang.months[month]} ${this.lang.toNumeral(year)}`;

        this.root.querySelector('.cal-month').value = month;
        this.root.querySelector('.cal-year').value  = year;

        const tbody      = this.root.querySelector('.cal-body');
        tbody.innerHTML  = '';

        const firstDay    = new Date(year, month, 1).getDay();   // 0 = Sun
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        let day = 1;

        // First row — may start mid-week
        let tr = document.createElement('tr');
        for (let i = 0; i < firstDay; i++) {
            tr.appendChild(document.createElement('td'));
        }
        for (let col = firstDay; col < 7; col++) {
            tr.appendChild(this.#buildCell(year, month, day++));
        }
        tbody.appendChild(tr);

        // Remaining rows
        while (day <= daysInMonth) {
            tr = document.createElement('tr');
            for (let c = 0; c < 7; c++) {
                tr.appendChild(
                    day <= daysInMonth
                        ? this.#buildCell(year, month, day++)
                        : document.createElement('td')
                );
            }
            tbody.appendChild(tr);
        }

        if (this.#ajaxConfig) {
            this.#fetchPostDays(year, month);
        }
    }

    #buildCell(year, month, day) {
        const td = document.createElement('td');
        td.dataset.day = day; // integer — used by AJAX post-day lookup
        td.textContent = this.lang.toNumeral(day);
        if (this.#isToday(year, month, day)) {
            td.classList.add('cal-today');
        }
        return td;
    }

    #isToday(year, month, day) {
        return this.today.getFullYear() === year &&
               this.today.getMonth()    === month &&
               this.today.getDate()     === day;
    }

    /* ── AJAX ──────────────────────────────────────── */
    async #fetchPostDays(year, month) {
        this.#setLoading(true);

        try {
            const res = await fetch(this.#ajaxConfig.url, {
                method:  'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body:    new URLSearchParams({
                    action: this.#ajaxConfig.action ?? 'cal_get_post_days',
                    nonce:  this.#ajaxConfig.nonce,
                    year,
                    month:  month + 1, // PHP expects 1-based month
                }),
            });
            if (!res.ok) { return; }

            const json = await res.json();
            const isCurrentView = this.currentYear === year && this.currentMonth === month;
            if (isCurrentView && json.success && json.data) {
                this.#markPostDays(json.data);
            }
        } catch {
            // silently fail — calendar remains functional without post links
        } finally {
            this.#setLoading(false);
        }
    }

    #setLoading(isLoading) {
        if (isLoading) {
            this.loadingCount++;
        } else {
            this.loadingCount = Math.max(0, this.loadingCount - 1);
        }

        const spinner = this.root.querySelector('.cal-spinner');
        if (spinner) {
            spinner.classList.toggle('is-active', this.loadingCount > 0);
        }
    }

    #markPostDays(postDays) {
        const tbody = this.root.querySelector('.cal-body');
        Object.entries(postDays).forEach(([day, url]) => {
            const td = tbody.querySelector(`[data-day="${day}"]`);
            if (!td) { return; }
            td.classList.add('cal-has-posts');
            const a   = document.createElement('a');
            a.href    = url;
            a.textContent = td.textContent;
            td.textContent = '';
            td.appendChild(a);
        });
    }


    /* ── Navigation ─────────────────────────────────── */
    prevMonth() {
        this.currentMonth--;
        if (this.currentMonth < 0) { this.currentMonth = 11; this.currentYear--; }
        this.render(this.currentYear, this.currentMonth);
    }

    nextMonth() {
        this.currentMonth++;
        if (this.currentMonth > 11) { this.currentMonth = 0; this.currentYear++; }
        this.render(this.currentYear, this.currentMonth);
    }

    /* ── Init ───────────────────────────────────────── */
    init() {
        this.root.style.setProperty('--cal-primary', this.primaryColor);
        if (this.#ajaxConfig?.spinnerUrl) {
            this.root.style.setProperty('--cal-spinner-url', `url("${this.#ajaxConfig.spinnerUrl}")`);
        }

        this.populateDropdowns();
        this.renderDayHeaders();
        this.render();

        this.root.querySelector('.cal-prev').addEventListener('click', () => this.prevMonth());
        this.root.querySelector('.cal-next').addEventListener('click', () => this.nextMonth());

        this.root.querySelector('.cal-month').addEventListener('change', (e) => {
            this.currentMonth = parseInt(e.target.value, 10);
            this.render(this.currentYear, this.currentMonth);
        });

        this.root.querySelector('.cal-year').addEventListener('change', (e) => {
            this.currentYear = parseInt(e.target.value, 10);
            this.render(this.currentYear, this.currentMonth);
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.ajax-cal-widget').forEach((root) => {
        const language = root.dataset.language === 'bn' ? 'bn' : 'en';
        const parsedYear = parseInt(root.dataset.startYear || '', 10);
        const nowYear = new Date().getFullYear();
        const startYear = Number.isInteger(parsedYear) && parsedYear > 0 ? parsedYear : nowYear - 10;

        new CalendarWidget({
            root,
            locale: language,
            startYear,
            primaryColor: '#2f855a',
            ajaxConfig: window.calWidgetConfig ?? null,
        }).init();
    });
});

