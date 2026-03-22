import './bootstrap';
import '../css/app.css';
import React from 'react';
import { createRoot } from 'react-dom/client';
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import Login from './pages/Auth/Login';
import Register from './pages/Auth/Register';
import Dashboard from './pages/Dashboard';
import Users from './pages/Users/Users';
import Orders from './pages/Orders/Orders';
import Payments from './pages/Payments/Payments';

const isAuthenticated = () => !!localStorage.getItem('token');

const ProtectedRoute = ({ children }) => {
    return isAuthenticated() ? children : <Navigate to="/login" />;
};

function App() {
    return (
        <Routes>
            <Route path="/login"    element={<Login />} />
            <Route path="/register" element={<Register />} />
            <Route path="/" element={
                <ProtectedRoute><Dashboard /></ProtectedRoute>
            } />
            <Route path="/users" element={
                <ProtectedRoute><Users /></ProtectedRoute>
            } />
            <Route path="/orders" element={
                <ProtectedRoute><Orders /></ProtectedRoute>
            } />
            <Route path="/payments" element={
                <ProtectedRoute><Payments /></ProtectedRoute>
            } />
        </Routes>
    );
}

const container = document.getElementById('app');
const root = createRoot(container);

root.render(
    <React.StrictMode>
        <BrowserRouter>
            <App />
        </BrowserRouter>
    </React.StrictMode>
);
