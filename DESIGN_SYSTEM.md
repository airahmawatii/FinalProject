# 🎨 TaskAcademia - Complete Design System

**Objective:** Konsistensi visual di seluruh sistem - elegan, smooth, tidak menyakitkan mata

---

## 🌈 Color Palette (Unified)

### Primary Colors (Indigo-Blue Theme)
```css
--color-primary-50: #eef2ff;   /* Very light indigo */
--color-primary-100: #e0e7ff;  /* Light indigo */
--color-primary-200: #c7d2fe;  /* Soft indigo */
--color-primary-300: #a5b4fc;  /* Medium indigo */
--color-primary-400: #818cf8;  /* Bright indigo */
--color-primary-500: #6366f1;  /* Main indigo */
--color-primary-600: #4f46e5;  /* Deep indigo */
--color-primary-700: #4338ca;  /* Darker indigo */
--color-primary-800: #3730a3;  /* Very dark indigo */
--color-primary-900: #312e81;  /* Almost black indigo */
```

### Secondary Colors (Blue Accent)
```css
--color-secondary-400: #60a5fa;  /* Light blue */
--color-secondary-500: #3b82f6;  /* Main blue */
--color-secondary-600: #2563eb;  /* Deep blue */
--color-secondary-700: #1d4ed8;  /* Darker blue */
```

### Background Colors (Dark Theme)
```css
--bg-primary: #0f172a;      /* slate-900 - Main background */
--bg-secondary: #1e293b;    /* slate-800 - Cards */
--bg-tertiary: #334155;     /* slate-700 - Hover states */
--bg-glass: rgba(255, 255, 255, 0.05);  /* Glass effect */
```

### Text Colors
```css
--text-primary: #ffffff;     /* White - Headings */
--text-secondary: #cbd5e1;   /* slate-300 - Body text */
--text-tertiary: #94a3b8;    /* slate-400 - Muted text */
--text-accent: #93c5fd;      /* blue-300 - Links/Accent */
```

### Status Colors
```css
--success: #10b981;   /* Green */
--warning: #f59e0b;   /* Amber */
--danger: #ef4444;    /* Red */
--info: #3b82f6;      /* Blue */
```

---

## 📐 Spacing & Sizing

### Border Radius
```css
--radius-sm: 0.5rem;    /* 8px */
--radius-md: 0.75rem;   /* 12px */
--radius-lg: 1rem;      /* 16px */
--radius-xl: 1.5rem;    /* 24px */
--radius-2xl: 2rem;     /* 32px */
--radius-full: 9999px;  /* Fully rounded */
```

### Shadows
```css
--shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
--shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
--shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
--shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
--shadow-glow: 0 0 20px rgba(99, 102, 241, 0.3);
```

---

## 🎭 Component Styles

### 1. Buttons
**Primary Button:**
```css
background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);
color: white;
padding: 0.75rem 1.5rem;
border-radius: 0.75rem;
font-weight: 600;
transition: all 0.2s ease;
box-shadow: 0 4px 6px rgba(79, 70, 229, 0.3);

hover: transform: translateY(-2px);
hover: box-shadow: 0 10px 15px rgba(79, 70, 229, 0.4);
```

**Secondary Button:**
```css
background: rgba(255, 255, 255, 0.05);
border: 1px solid rgba(255, 255, 255, 0.1);
color: #cbd5e1;
backdrop-filter: blur(10px);

hover: background: rgba(255, 255, 255, 0.1);
hover: color: white;
```

### 2. Cards
```css
background: rgba(255, 255, 255, 0.05);
backdrop-filter: blur(20px);
border: 1px solid rgba(255, 255, 255, 0.1);
border-radius: 1.5rem;
padding: 1.5rem;
transition: all 0.3s ease;

hover: transform: translateY(-4px);
hover: box-shadow: 0 20px 25px rgba(0, 0, 0, 0.15);
```

### 3. Inputs
```css
background: rgba(255, 255, 255, 0.05);
border: 1px solid rgba(255, 255, 255, 0.1);
border-radius: 0.75rem;
padding: 0.75rem 1rem;
color: white;
transition: all 0.2s ease;

focus: border-color: #4f46e5;
focus: box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
focus: background: rgba(255, 255, 255, 0.08);
```

### 4. Tables
```css
/* Table Header */
background: rgba(255, 255, 255, 0.03);
border-bottom: 1px solid rgba(255, 255, 255, 0.1);
text-transform: uppercase;
font-size: 0.75rem;
font-weight: 700;
color: #94a3b8;

/* Table Row */
border-bottom: 1px solid rgba(255, 255, 255, 0.05);
transition: background 0.15s ease;

hover: background: rgba(79, 70, 229, 0.05);
```

---

## 🎬 Animations (Smooth, No "Kejang-kejang")

### Transitions
```css
/* Default - Fast but smooth */
transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);

/* Hover effects - Slightly slower */
transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);

/* Layout changes - Smooth */
transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
```

### Hover Effects (Subtle)
```css
/* Cards */
hover: transform: translateY(-4px);  /* Smooth lift */

/* Buttons */
hover: transform: translateY(-2px);  /* Subtle lift */
active: transform: scale(0.98);      /* Gentle press */

/* Links */
hover: color: white;
hover: text-decoration: none;
```

### NO Jarring Animations
❌ **Avoid:**
- Sudden color changes
- Fast rotations
- Abrupt scale changes
- Instant transitions

✅ **Use:**
- Smooth easing functions
- Gradual transforms
- Gentle opacity changes
- Consistent timing

---

## 📱 Responsive Breakpoints

```css
/* Mobile First */
sm: 640px   /* Small devices */
md: 768px   /* Tablets */
lg: 1024px  /* Laptops */
xl: 1280px  /* Desktops */
2xl: 1536px /* Large screens */
```

---

## 🎯 Implementation Priority

### Phase 1: Core Components (NOW)
1. ✅ Sidebar (Admin, Dosen, Mahasiswa)
2. ✅ Global CSS Theme
3. ⏳ Buttons (All pages)
4. ⏳ Cards (Dashboard, Lists)
5. ⏳ Forms (Add, Edit pages)

### Phase 2: Pages
6. ⏳ Dashboard (Admin, Dosen, Mahasiswa)
7. ⏳ Tables (User list, Course list, etc)
8. ⏳ Modals & Alerts
9. ⏳ Login & Auth pages

### Phase 3: Polish
10. ⏳ Loading states
11. ⏳ Empty states
12. ⏳ Error states
13. ⏳ Success messages

---

## 🚫 Design Don'ts

❌ **Never:**
- Mix different color schemes
- Use jarring animations
- Create inconsistent spacing
- Ignore hover states
- Forget focus states (accessibility)
- Use pure black (#000000)
- Use pure white backgrounds in dark theme

✅ **Always:**
- Use color variables
- Smooth transitions (0.2s - 0.3s)
- Consistent border radius
- Glass morphism for depth
- Subtle shadows
- Test on mobile

---

## 📝 Code Standards

### CSS Class Naming
```html
<!-- Component-based -->
<div class="card">
<button class="btn btn-primary">
<input class="form-input">

<!-- State-based -->
<div class="card hover:shadow-lg">
<button class="btn active:scale-95">
```

### Tailwind Utilities (Preferred)
```html
<!-- Use Tailwind for consistency -->
<div class="bg-white/5 backdrop-blur-md rounded-xl p-6 border border-white/10">
```

---

**Generated by:** Antigravity AI  
**Date:** 2025-12-20  
**Status:** Living Document
