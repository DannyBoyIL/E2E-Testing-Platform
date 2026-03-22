import React from 'react';
import { Link } from 'react-router-dom';

export default function Dashboard() {
    return (
        <div className="min-h-screen bg-gray-100 p-8">
            <h1 className="text-3xl font-bold mb-6">E2E Testing Platform</h1>
            <div className="grid grid-cols-3 gap-4">
                <Link to="/users" className="bg-white p-6 rounded shadow hover:shadow-md text-center">Users</Link>
                <Link to="/orders" className="bg-white p-6 rounded shadow hover:shadow-md text-center">Orders</Link>
                <Link to="/payments" className="bg-white p-6 rounded shadow hover:shadow-md text-center">Payments</Link>
            </div>
        </div>
    );
}
