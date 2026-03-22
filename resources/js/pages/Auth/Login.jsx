import React, { useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import api from '../../api/axios';

export default function Login() {
    const [form, setForm] = useState({ email: '', password: '' });
    const [error, setError] = useState('');
    const navigate = useNavigate();

    const handleSubmit = async (e) => {
        e.preventDefault();
        try {
            const res = await api.post('/auth/login', form);
            localStorage.setItem('token', res.data.token);
            navigate('/');
        } catch {
            setError('Invalid credentials');
        }
    };

    return (
        <div className="min-h-screen flex items-center justify-center bg-gray-100">
            <div className="bg-white p-8 rounded shadow w-full max-w-md">
                <h2 className="text-2xl font-bold mb-6">Login</h2>
                {error && <p className="text-red-500 mb-4">{error}</p>}
                <form onSubmit={handleSubmit} className="space-y-4">
                    <input type="email" placeholder="Email" className="w-full border p-2 rounded"
                        value={form.email} onChange={e => setForm({...form, email: e.target.value})} />
                    <input type="password" placeholder="Password" className="w-full border p-2 rounded"
                        value={form.password} onChange={e => setForm({...form, password: e.target.value})} />
                    <button type="submit" className="w-full bg-blue-600 text-white p-2 rounded hover:bg-blue-700">
                        Login
                    </button>
                </form>
                <p className="mt-4 text-center">No account? <Link to="/register" className="text-blue-600">Register</Link></p>
            </div>
        </div>
    );
}
