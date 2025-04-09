import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';

const Login = () => {
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [error, setError] = useState('');
    const [loading, setLoading] = useState(false);
    const [debug, setDebug] = useState('');
    const { login } = useAuth();
    const navigate = useNavigate();

    const handleSubmit = async (e) => {
        e.preventDefault();
        setError('');
        setDebug('');
        setLoading(true);

        try {
            setDebug('Attempting login...');
            await login(email, password);
            setDebug('Login successful, redirecting...');
            navigate('/');
        } catch (err) {
            setDebug(`Error: ${JSON.stringify(err.response?.data || err.message || err)}`);

            // Get detailed error message
            let errorMessage = 'Failed to login. Please check your credentials.';

            if (err.response?.data?.message) {
                errorMessage = err.response.data.message;
            } else if (err.response?.data?.error) {
                errorMessage = err.response.data.error;
            } else if (err.message) {
                errorMessage = err.message;
            }

            setError(errorMessage);
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="min-h-screen flex items-center justify-center bg-gray-50">
            <div className="max-w-md w-full p-6 bg-white rounded shadow-md">
                <h2 className="text-2xl font-bold mb-6 text-center">Login / Register</h2>

                {error && (
                    <div className="mb-4 p-3 bg-red-100 text-red-700 rounded">
                        {error}
                    </div>
                )}

                {debug && (
                    <div className="mb-4 p-3 bg-blue-100 text-blue-700 rounded text-sm">
                        <strong>Debug:</strong> {debug}
                    </div>
                )}

                <form onSubmit={handleSubmit}>
                    <div className="mb-4">
                        <label className="block text-gray-700 mb-2" htmlFor="email">
                            Email
                        </label>
                        <input
                            id="email"
                            type="email"
                            className="w-full p-2 border rounded"
                            value={email}
                            onChange={(e) => setEmail(e.target.value)}
                            required
                        />
                    </div>

                    <div className="mb-6">
                        <label className="block text-gray-700 mb-2" htmlFor="password">
                            Password
                        </label>
                        <input
                            id="password"
                            type="password"
                            className="w-full p-2 border rounded"
                            value={password}
                            onChange={(e) => setPassword(e.target.value)}
                            required
                        />
                    </div>

                    <button
                        type="submit"
                        className="w-full bg-blue-500 text-white p-2 rounded hover:bg-blue-600"
                        disabled={loading}
                    >
                        {loading ? 'Loading...' : 'Login / Register'}
                    </button>

                    <p className="mt-4 text-sm text-center text-gray-600">
                        If you don't have an account, it will be created automatically.
                    </p>

                    <div className="mt-4 text-xs text-gray-500">
                        <strong>API URL:</strong> http://localhost:86/api
                    </div>
                </form>
            </div>
        </div>
    );
};

export default Login;
