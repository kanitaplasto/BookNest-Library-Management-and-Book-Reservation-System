'use strict';

let books = [];
let reservations = [];
let routeToken = 0;

const app = document.querySelector('#app');

// API endpoint
const API_URL = '../backend/reservation-api.php';

// Demo user ID from the BookNest database
const CURRENT_USER_ID = 3;

// Escape HTML to prevent unsafe content from being inserted
const esc = value => String(value ?? '').replace(
    /[&<>"']/g,
    c => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;'
    }[c])
);

// Display notification
function toast(message) {
    const el = document.querySelector('#toast');

    if (!el) {
        console.log(message);
        return;
    }

    el.textContent = message;
    el.style.display = 'block';

    clearTimeout(toast.timer);

    toast.timer = setTimeout(
        () => el.style.display = 'none',
        4200
    );
}

// Book cover
function cover(b) {
    const color = /^#[a-f0-9]{6}$/i.test(b.color || '')
        ? b.color
        : '#244e42';

    return `
        <div class="cover" style="background:${color}">
            <b>${esc(b.title)}</b>
            <small>${esc(b.author)}</small>
        </div>
    `;
}

// Book cards
function cards(list) {
    return list.length
        ? list.map(b => `
            <article class="book">
                ${cover(b)}

                <h3>${esc(b.title)}</h3>

                <p>
                    ${esc(b.author)} · ${esc(b.category)}
                </p>

                <p>
                    <span class="badge ${
                        b.status === 'Available' ? '' : 'busy'
                    }">
                        ${esc(b.status)}
                    </span>
                </p>

                <a class="button secondary" href="#book/${b.id}">
                    View book →
                </a>
            </article>
        `).join('')
        : '<p class="empty">No matching books. Try another search.</p>';
}

// Make API requests and handle errors
async function apiRequest(url, options = {}) {
    const response = await fetch(url, {
        ...options,
        headers: {
            'Accept': 'application/json',
            ...(options.body
                ? { 'Content-Type': 'application/json' }
                : {}),
            ...options.headers
        },
        cache: 'no-store'
    });

    const text = await response.text();

    let data;

    try {
        data = text ? JSON.parse(text) : {};
    } catch {
        throw new Error(
            'The PHP API returned an invalid response. Check the PHP code.'
        );
    }

    if (!response.ok) {
        throw new Error(
            data.error ||
            data.message ||
            `Request failed with status ${response.status}.`
        );
    }

    return data;
}

// Load reservations from MySQL through the PHP API
async function loadReservations() {
    const tbody = document.querySelector('#reservations-body');

    if (!tbody) {
        return;
    }

    tbody.innerHTML = `
        <tr>
            <td colspan="5">Loading reservations...</td>
        </tr>
    `;

    try {
        const data = await apiRequest(
            `${API_URL}?user_id=${CURRENT_USER_ID}`
        );

        if (!Array.isArray(data)) {
            throw new Error(
                'The API did not return a reservation list.'
            );
        }

        reservations = data;

        renderReservations();

        const count = document.querySelector('#reservation-count');

        if (count) {
            count.textContent = reservations.filter(
                r => r.status !== 'Cancelled'
            ).length;
        }

    } catch (error) {
        console.error('Could not load reservations:', error);

        tbody.innerHTML = `
            <tr>
                <td colspan="5">
                    Could not load reservations.
                    Check the PHP API and database connection.
                </td>
            </tr>
        `;

        toast(error.message);
    }
}

// Display reservations
function renderReservations() {
    const tbody = document.querySelector('#reservations-body');

    if (!tbody) {
        return;
    }

    if (reservations.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5">
                    No reservations yet. Browse the catalog to reserve a book.
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = reservations.map(r => {
        const id = r.id;
        const status = r.status || 'Queued';
        const cancelled = status === 'Cancelled';

        return `
            <tr>
                <td>${esc(id ?? '—')}</td>

                <td>${esc(r.book_title || 'Unknown book')}</td>

                <td>${esc(r.reservation_date || '—')}</td>

                <td>
                    <span class="badge ${cancelled ? 'busy' : ''}">
                        ${esc(status)}
                    </span>
                </td>

                <td>
                    ${
                        cancelled
                            ? '<span class="muted">Cancelled</span>'
                            : `
                                <button
                                    type="button"
                                    class="secondary cancel-reservation"
                                    data-id="${esc(id)}">
                                    Cancel
                                </button>
                            `
                    }
                </td>
            </tr>
        `;
    }).join('');

    // Attach event listeners to Cancel buttons
    tbody.querySelectorAll('.cancel-reservation').forEach(button => {
        button.addEventListener('click', async () => {
            const reservationId = button.dataset.id;

            if (!reservationId) {
                toast('Reservation ID is missing.');
                return;
            }

            const confirmed = confirm(
                'Are you sure you want to cancel this reservation?'
            );

            if (!confirmed) {
                return;
            }

            button.disabled = true;
            button.textContent = 'Cancelling...';

            try {
                await apiRequest(API_URL, {
                    method: 'POST',
                    body: JSON.stringify({
                        action: 'cancel',
                        id: Number(reservationId)
                    })
                });

                toast('Reservation cancelled successfully.');

                // Reload the list from the database
                await loadReservations();

            } catch (error) {
                console.error('Cancellation failed:', error);

                toast(
                    'Could not cancel reservation: ' + error.message
                );

                button.disabled = false;
                button.textContent = 'Cancel';
            }
        });
    });
}

// Create a reservation in the database
async function createReservation(book) {
    const existingReservation = reservations.some(r =>
        Number(r.book_id) === Number(book.id) &&
        r.status !== 'Cancelled'
    );

    if (existingReservation) {
        toast('You already have an active reservation for this book.');
        return;
    }

    const activeReservations = reservations.filter(
        r => r.status !== 'Cancelled'
    );

    if (activeReservations.length >= 3) {
        toast('Reservation limit reached: up to 3 active reservations.');
        return;
    }

    try {
        const result = await apiRequest(API_URL, {
            method: 'POST',
            body: JSON.stringify({
                user_id: CURRENT_USER_ID,
                book_id: Number(book.id),
                reservation_date: new Date()
                    .toISOString()
                    .slice(0, 19)
                    .replace('T', ' '),
                status: 'Queued'
            })
        });

        toast(
            `Reservation created successfully. ID: ${result.id ?? 'created'}`
        );

        // Refresh the database data
        await loadReservations();

    } catch (error) {
        console.error('Could not create reservation:', error);

        toast(
            'Could not create reservation: ' + error.message
        );
    }
}

// Navigation
async function navigate() {
    const token = ++routeToken;

    const [raw, id] = (
        location.hash.slice(1) || 'dashboard'
    ).split('/');

    const route = [
        'dashboard',
        'catalog',
        'book',
        'reservations',
        'loans',
        'profile',
        'discover',
        'admin',
        'login',
        'register'
    ].includes(raw) ? raw : null;

    document.querySelectorAll('nav a').forEach(a =>
        a.classList.toggle(
            'active',
            a.hash === '#' + (
                route === 'book' ? 'catalog' : route
            )
        )
    );

    if (!route) {
        app.innerHTML = `
            <h1>Page not found</h1>
            <a class="button" href="#dashboard">
                Back to overview
            </a>
        `;
        return;
    }

    try {
        const response = await fetch(`views/${route}.html`);

        if (!response.ok) {
            throw new Error('Template unavailable.');
        }

        const template = await response.text();

        if (token !== routeToken) {
            return;
        }

        app.innerHTML = template;

        document.title =
            `BookNest | ${route[0].toUpperCase() + route.slice(1)}`;

        // Dashboard
        if (route === 'dashboard') {
            const featured = document.querySelector('#featured');

            if (featured) {
                featured.innerHTML = cards(books.slice(0, 4));
            }

            const count = document.querySelector('#reservation-count');

            if (count) {
                count.textContent = reservations.filter(
                    r => r.status !== 'Cancelled'
                ).length;
            }
        }

        // Catalog
        if (route === 'catalog') {
            const filter = () => {
                const searchInput = document.querySelector('#search');
                const categoryInput = document.querySelector('#category');
                const availabilityInput =
                    document.querySelector('#availability');

                if (!searchInput || !categoryInput || !availabilityInput) {
                    return;
                }

                const q = searchInput.value.toLowerCase().trim();
                const category = categoryInput.value;
                const status = availabilityInput.value;

                const result = books.filter(b =>
                    (b.title + ' ' + b.author)
                        .toLowerCase()
                        .includes(q) &&
                    (!category || b.category === category) &&
                    (!status || b.status === status)
                );

                const grid = document.querySelector('#catalog-grid');

                if (grid) {
                    grid.innerHTML = cards(result);
                }

                const resultCount =
                    document.querySelector('#result-count');

                if (resultCount) {
                    resultCount.textContent =
                        `${result.length} books in this selection`;
                }
            };

            ['search', 'category', 'availability'].forEach(id => {
                const element = document.getElementById(id);

                if (element) {
                    element.addEventListener('input', filter);
                    element.addEventListener('change', filter);
                }
            });

            filter();
        }

        // Book details
        if (route === 'book') {
            const book = books.find(b => b.id === Number(id));
            const detail = document.querySelector('#book-detail');

            if (!book) {
                if (detail) {
                    detail.innerHTML = '<h1>Book not found</h1>';
                }

                return;
            }

            if (detail) {
                detail.innerHTML = `
                    <div class="details">
                        ${cover(book)}

                        <div>
                            <div class="eyebrow">
                                ${esc(book.category)}
                            </div>

                            <h1>${esc(book.title)}</h1>

                            <p class="sub">
                                by ${esc(book.author)}
                            </p>

                            <span class="badge">
                                ${esc(book.status)}
                            </span>

                            <p>
                                Explore this title from our curated library
                                collection. Detailed descriptions, editions
                                and copy information will be provided by
                                the database in a later milestone.
                            </p>

                            <p>
                                Collection: Main library<br>
                                Loan period: 14 days · sample policy
                            </p>

                            <button
                                id="reserve"
                                ${book.status !== 'Available' ? 'disabled' : ''}>
                                ${
                                    book.status === 'Available'
                                        ? 'Reserve this book'
                                        : 'Currently unavailable'
                                }
                            </button>
                        </div>
                    </div>
                `;
            }

            const reserveButton = document.querySelector('#reserve');

            if (reserveButton) {
                reserveButton.addEventListener('click', async () => {
                    reserveButton.disabled = true;

                    await createReservation(book);

                    reserveButton.disabled = false;
                });
            }
        }

        // Reservations
        if (route === 'reservations') {
            await loadReservations();
        }

        // Loans
        if (route === 'loans') {
            const renewButton = document.querySelector('#renew');

            if (renewButton) {
                renewButton.onclick = e => {
                    const dueDate = document.querySelector('#due-date');

                    if (dueDate) {
                        dueDate.textContent = '13 Nov 2026 · demo';
                    }

                    e.target.disabled = true;

                    toast(
                        'Renewal preview: 14 days added. No real loan changed.'
                    );
                };
            }
        }

        // Discover
        if (route === 'discover') {
            const search = () => {
                const searchInput =
                    document.querySelector('#discover-search');

                const results =
                    document.querySelector('#discover-results');

                if (!searchInput || !results) {
                    return;
                }

                const q = searchInput.value.toLowerCase().trim();

                results.innerHTML = cards(
                    books.filter(b =>
                        (b.title + ' ' + b.author)
                            .toLowerCase()
                            .includes(q)
                    ).slice(0, 4)
                );
            };

            const discoverButton =
                document.querySelector('#discover-button');

            const discoverSearch =
                document.querySelector('#discover-search');

            if (discoverButton) {
                discoverButton.onclick = search;
            }

            if (discoverSearch) {
                discoverSearch.onkeydown = e => {
                    if (e.key === 'Enter') {
                        search();
                    }
                };
            }

            search();
        }

        // Profile
        if (route === 'profile') {
            const profileForm =
                document.querySelector('#profile-form');

            if (profileForm) {
                profileForm.onsubmit = e => {
                    e.preventDefault();

                    toast(
                        'Profile preview validated. Changes are not saved.'
                    );
                };
            }
        }

        // Login demo
        if (route === 'login') {
            const loginForm =
                document.querySelector('#login-form');

            if (loginForm) {
                loginForm.onsubmit = e => {
                    e.preventDefault();
                    e.target.reset();

                    location.hash = 'dashboard';

                    toast(
                        'Demo member space opened. No authentication performed.'
                    );
                };
            }
        }

        // Registration demo
        if (route === 'register') {
            const pass = document.querySelector('#password');
            const confirm =
                document.querySelector('#confirm-password');

            if (pass && confirm) {
                const validate = () => {
                    confirm.setCustomValidity(
                        pass.value === confirm.value
                            ? ''
                            : 'Passwords must match.'
                    );
                };

                pass.oninput = confirm.oninput = validate;
            }

            const registerForm =
                document.querySelector('#register-form');

            if (registerForm) {
                registerForm.onsubmit = e => {
                    e.preventDefault();
                    e.target.reset();

                    location.hash = 'login';

                    toast(
                        'Registration preview complete. No account was created.'
                    );
                };
            }
        }

        // Admin demo
        if (route === 'admin') {
            const adminBooks =
                document.querySelector('#admin-books');

            const inventory = () => {
                if (!adminBooks) {
                    return;
                }

                adminBooks.innerHTML = books.map(b => `
                    <tr>
                        <td>${esc(b.title)}</td>
                        <td>${esc(b.category)}</td>
                        <td>${esc(b.status)}</td>
                    </tr>
                `).join('');
            };

            inventory();

            const adminForm =
                document.querySelector('#admin-form');

            if (adminForm) {
                adminForm.onsubmit = e => {
                    e.preventDefault();

                    const data = new FormData(e.target);

                    books.push({
                        id: Math.max(...books.map(b => b.id)) + 1,
                        title: data.get('title').trim(),
                        author: data.get('author').trim(),
                        category: data.get('category'),
                        status: 'Available',
                        color: '#244e42'
                    });

                    inventory();
                    e.target.reset();

                    toast(
                        'Demo book added to the in-memory catalog.'
                    );
                };
            }

            const circulationPreview =
                document.querySelector('#circulation-preview');

            if (circulationPreview) {
                circulationPreview.onclick = () =>
                    toast(
                        'Issue / return workflow planned for a later milestone.'
                    );
            }
        }

        app.focus({ preventScroll: true });
        window.scrollTo(0, 0);

    } catch (error) {
        console.error('Navigation error:', error);

        if (token === routeToken) {
            app.innerHTML = `
                <h1>Unable to load this page</h1>
                <p>
                    Run the project through Apache or another local HTTP
                    server. Check README.md for instructions.
                </p>
            `;
        }
    }
}

// Load books and start the application
fetch('assets/books.json')
    .then(response => {
        if (!response.ok) {
            throw new Error('Could not load books.json.');
        }

        return response.json();
    })
    .then(data => {
        books = data;

        window.addEventListener('hashchange', navigate);

        navigate();
    })
    .catch(error => {
        console.error('Error loading books:', error);

        app.innerHTML = `
            <h1>Unable to load BookNest</h1>
            <p>
                Check that assets/books.json exists and start the project
                using a local HTTP server.
            </p>
        `;
    });