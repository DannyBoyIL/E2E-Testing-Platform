import React, { useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import api from '../../api/axios';

export default function Register() {
    const [form, setForm] = useState({ name: '', email: '', password: '', password_confirmation: '' });
    const [error, setError] = useState('');
    const navigate = useNavigate();

    const handleSubmit = async (e) => {
        e.preventDefault();
        try {
            const res = await api.post('/auth/register', form);
            localStorage.setItem('token', res.data.token);
            navigate('/');
        } catch {
            setError('Registration failed. Please check your details.');
        }
    };

    return (
        <div className="min-h-screen flex items-center justify-center bg-gray-100">
            <div className="bg-white p-8 rounded shadow w-full max-w-md">
                <h2 className="text-2xl font-bold mb-6">Register</h2>
                {error && <p className="text-red-500 mb-4">{error}</p>}
                <form onSubmit={handleSubmit} className="space-y-4">
                    <input type="text" placeholder="Name" className="w-full border p-2 rounded"
                        value={form.name} onChange={e => setForm({...form, name: e.target.value})} />
                    <input type="email" placeholder="Email" className="w-full border p-2 rounded"
                        value={form.email} onChange={e => setForm({...form, email: e.target.value})} />
                    <input type="password" placeholder="Password" className="w-full border p-2 rounded"
                        value={form.password} onChange={e => setForm({...form, password: e.target.value})} />
                    <input type="password" placeholder="Confirm Password" className="w-full border p-2 rounded"
                        value={form.password_confirmation} onChange={e => setForm({...form, password_confirmation: e.target.value})} />
                    <button type="submit" className="w-full bg-green-600 text-white p-2 rounded hover:bg-green-700">
                        Register
                    </button>
                </form>
                <p className="mt-4 text-center">Have an account? <Link to="/login" className="text-blue-600">Login</Link></p>
            </div>
        </div>
    );
}
