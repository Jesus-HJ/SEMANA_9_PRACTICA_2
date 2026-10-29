(() => {
    const form = document.querySelector('.booking-card form');
    if (!form) return;

    const arrival = form.querySelector('#check_in');
    const departure = form.querySelector('#check_out');
    const room = form.querySelector('#room');
    const guests = form.querySelector('#guests');
    const nightCount = form.querySelector('#night-count');
    const stayTotal = form.querySelector('#stay-total');
    const menuButton = document.querySelector('.menu-toggle');
    const navigation = document.querySelector('.site-nav');
    const formAlert = document.querySelector('.booking-card .form-alert[role="alert"]');

    if (formAlert) {
        formAlert.scrollIntoView({ block: 'center' });
    }

    const localDate = (value) => {
        if (!value) return null;
        const [year, month, day] = value.split('-').map(Number);
        return new Date(Date.UTC(year, month - 1, day));
    };

    const updateSummary = () => {
        const selectedRoom = room.selectedOptions[0];
        const rate = Number(selectedRoom.dataset.rate);
        const capacity = Number(selectedRoom.dataset.capacity);
        const checkInDate = localDate(arrival.value);
        const checkOutDate = localDate(departure.value);
        const nights = checkInDate && checkOutDate
            ? Math.round((checkOutDate - checkInDate) / 86400000)
            : 0;

        for (const option of guests.options) {
            option.disabled = Number(option.value) > capacity;
        }
        if (Number(guests.value) > capacity) guests.value = String(capacity);

        nightCount.textContent = `${Math.max(nights, 1)} ${nights === 1 ? 'noche' : 'noches'}`;
        const total = Math.max(nights, 1) * rate;
        const formattedTotal = total.toLocaleString('es-PE').replace(/,/g, '.');
        stayTotal.textContent = `S/ ${formattedTotal}`;
        departure.min = arrival.value
            ? new Date(Date.UTC(checkInDate.getUTCFullYear(), checkInDate.getUTCMonth(), checkInDate.getUTCDate() + 1)).toISOString().slice(0, 10)
            : departure.min;
        if (arrival.value && departure.value && departure.value <= arrival.value) {
            departure.value = departure.min;
        }
    };

    for (const input of [arrival, departure, room, guests]) {
        input.addEventListener('change', updateSummary);
    }

    document.querySelectorAll('[data-room-choice]').forEach((link) => {
        link.addEventListener('click', () => {
            room.value = link.dataset.roomChoice;
            updateSummary();
        });
    });

    if (menuButton && navigation) {
        menuButton.addEventListener('click', () => {
            const expanded = menuButton.getAttribute('aria-expanded') === 'true';
            menuButton.setAttribute('aria-expanded', String(!expanded));
            navigation.classList.toggle('is-open', !expanded);
        });
        navigation.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', () => {
                menuButton.setAttribute('aria-expanded', 'false');
                navigation.classList.remove('is-open');
            });
        });
    }

    updateSummary();
})();
