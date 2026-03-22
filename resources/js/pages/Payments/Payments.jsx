import React, { useEffect, useState } from 'react';
import api from '../../api/axios';

export default function Payments() {
    const [payments, setPayments] = useState([]);

    useEffect(() => {
        api.get('/payments').then(res => setPayments(res.data.data));
    }, []);

    return (
        <div className="min-h-screen bg-gray-100 p-8">
            <h1 className="text-2xl font-bold mb-6">Payments</h1>
            <div className="bg-white rounded shadow overflow-hidden">
                <table className="w-full">
                    <thead className="bg-gray-50">
                        <tr>
                            <th className="p-4 text-left">ID</th>
                            <th className="p-4 text-left">Order</th>
                            <th className="p-4 text-left">Amount</th>
                            <th className="p-4 text-left">Status</th>
                            <th className="p-4 text-left">Method</th>
                        </tr>
                    </thead>
                    <tbody>
                        {payments.map(payment => (
                            <tr key={payment.id} className="border-t">
                                <td className="p-4">{payment.id}</td>
                                <td className="p-4">#{payment.order_id}</td>
                                <td className="p-4">${payment.amount}</td>
                                <td className="p-4">{payment.status}</td>
                                <td className="p-4">{payment.payment_method}</td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </div>
    );
}
