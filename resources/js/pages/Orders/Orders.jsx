import React, { useEffect, useState } from 'react';
import api from '../../api/axios';

export default function Orders() {
    const [orders, setOrders] = useState([]);

    useEffect(() => {
        api.get('/orders').then(res => setOrders(res.data.data));
    }, []);

    return (
        <div className="min-h-screen bg-gray-100 p-8">
            <h1 className="text-2xl font-bold mb-6">Orders</h1>
            <div className="bg-white rounded shadow overflow-hidden">
                <table className="w-full">
                    <thead className="bg-gray-50">
                        <tr>
                            <th className="p-4 text-left">ID</th>
                            <th className="p-4 text-left">Status</th>
                            <th className="p-4 text-left">Total</th>
                            <th className="p-4 text-left">Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        {orders.map(order => (
                            <tr key={order.id} className="border-t">
                                <td className="p-4">{order.id}</td>
                                <td className="p-4">{order.status}</td>
                                <td className="p-4">${order.total}</td>
                                <td className="p-4">{order.notes}</td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </div>
    );
}
