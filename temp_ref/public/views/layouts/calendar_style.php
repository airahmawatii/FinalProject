<style>
    /* Premium FullCalendar Overrides */
    .fc {
        font-family: 'Outfit', sans-serif;
        --fc-border-color: rgba(255, 255, 255, 0.1);
        --fc-button-text-color: #1e293b;
        --fc-button-bg-color: #ffffff;
        --fc-button-border-color: #e2e8f0;
        --fc-button-hover-bg-color: #f1f5f9;
        --fc-button-hover-border-color: #cbd5e1;
        --fc-button-active-bg-color: #e2e8f0;
        --fc-button-active-border-color: #cbd5e1;
        --fc-event-bg-color: #3b82f6;
        --fc-event-border-color: #3b82f6;
        --fc-today-bg-color: rgba(59, 130, 246, 0.1);
        --fc-neutral-bg-color: rgba(255, 255, 255, 0.5);
    }

    /* Header Toolbar */
    .fc-toolbar-title {
        font-size: 1.1rem !important;
        font-weight: 700;
        color: #1e293b;
    }
    
    .fc-button {
        font-size: 0.75rem !important;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border-radius: 0.5rem !important;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        padding: 0.4rem 0.8rem !important;
    }

    /* Table & Grid */
    .fc-theme-standard th {
        border: none;
        padding: 8px 0;
        font-size: 0.75rem;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
    }
    
    .fc-theme-standard td {
        border: 1px solid rgba(0, 0, 0, 0.05);
    }
    
    .fc-daygrid-day-number {
        font-size: 0.85rem;
        font-weight: 500;
        color: #475569;
        padding: 8px !important;
    }

    /* Events */
    .fc-daygrid-event {
        border-radius: 6px;
        padding: 2px 6px;
        font-size: 0.75rem;
        font-weight: 600;
        transition: transform 0.2s;
        border: none;
    }
    .fc-daygrid-event:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }
    .fc-event-main {
        color: white;
    }

    /* Today Highlight */
    .fc-day-today {
        background-color: #eff6ff !important; /* blue-50 */
    }
</style>
