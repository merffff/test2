import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { searchAddresses, saveAddress, getSavedAddresses } from '../services/api';
import { useAuth } from '../contexts/AuthContext';

const Home = () => {
    const [query, setQuery] = useState('');
    const [searchResults, setSearchResults] = useState([]);
    const [savedAddresses, setSavedAddresses] = useState([]);
    const [loading, setLoading] = useState(false);
    const [saveLoading, setSaveLoading] = useState({});
    const [error, setError] = useState('');
    const [success, setSuccess] = useState('');
    const { user, logout } = useAuth();
    const navigate = useNavigate();

    useEffect(() => {
        loadSavedAddresses();
    }, []);

    const loadSavedAddresses = async () => {
        try {
            setLoading(true);
            const response = await getSavedAddresses();
            setSavedAddresses(response.data);
        } catch (err) {
            setError('Failed to load saved addresses');
            console.error(err);
        } finally {
            setLoading(false);
        }
    };

    const handleSearch = async () => {
        if (query.trim().length < 3) {
            setError('Search query must be at least 3 characters');
            return;
        }

        try {
            setLoading(true);
            setError('');
            setSuccess('');
            const response = await searchAddresses(query);
            setSearchResults(response.data.suggestions || []);
        } catch (err) {
            setError('Failed to search addresses');
            console.error(err);
        } finally {
            setLoading(false);
        }
    };

    const handleSaveAddress = async (address) => {
        try {
            setSaveLoading(prev => ({ ...prev, [address.value]: true }));
            setError('');
            setSuccess('');

            const response = await saveAddress(address.value, address);

            // Refresh saved addresses list
            await loadSavedAddresses();

            setSuccess('Address saved successfully!');

            // Clear search results
            setSearchResults([]);
            setQuery('');
        } catch (err) {
            setError(err.response?.data?.message || 'Failed to save address');
            console.error(err);
        } finally {
            setSaveLoading(prev => ({ ...prev, [address.value]: false }));
        }
    };

    const handleLogout = async () => {
        await logout();
        navigate('/login');
    };

    const handleQueryChange = (e) => {
        setQuery(e.target.value);
        if (e.target.value.trim().length >= 3) {
            handleSearch();
        } else {
            setSearchResults([]);
        }
    };

    return (
        <div className="container mx-auto p-4 max-w-4xl">
            <div className="flex justify-between items-center mb-8">
                <h1 className="text-2xl font-bold">Address Search</h1>
                <div className="flex items-center">
                    <span className="mr-4">{user?.email}</span>
                    <button
                        onClick={handleLogout}
                        className="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600"
                    >
                        Logout
                    </button>
                </div>
            </div>

            {error && (
                <div className="mb-4 p-3 bg-red-100 text-red-700 rounded">
                    {error}
                </div>
            )}

            {success && (
                <div className="mb-4 p-3 bg-green-100 text-green-700 rounded">
                    {success}
                </div>
            )}

            <div className="mb-8">
                <div className="mb-4">
                    <label htmlFor="address" className="block text-gray-700 mb-2">
                        Search Address
                    </label>
                    <div className="flex">
                        <input
                            id="address"
                            type="text"
                            className="flex-grow p-2 border rounded-l"
                            placeholder="Start typing an address..."
                            value={query}
                            onChange={handleQueryChange}
                        />
                        <button
                            onClick={handleSearch}
                            className="bg-blue-500 text-white px-4 py-2 rounded-r hover:bg-blue-600"
                            disabled={loading || query.trim().length < 3}
                        >
                            {loading ? 'Searching...' : 'Search'}
                        </button>
                    </div>
                </div>

                {searchResults.length > 0 && (
                    <div className="border rounded p-4 bg-gray-50">
                        <h2 className="text-lg font-semibold mb-4">Search Results</h2>
                        <ul className="divide-y">
                            {searchResults.map((address) => (
                                <li key={address.value} className="py-3">
                                    <div className="flex justify-between items-center">
                                        <div>
                                            <p className="font-medium">{address.value}</p>
                                            {address.data && (
                                                <p className="text-sm text-gray-600">
                                                    {address.data.postal_code && `${address.data.postal_code}, `}
                                                    {address.data.city || address.data.region}
                                                </p>
                                            )}
                                        </div>
                                        <button
                                            onClick={() => handleSaveAddress(address)}
                                            className="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600"
                                            disabled={saveLoading[address.value]}
                                        >
                                            {saveLoading[address.value] ? 'Saving...' : 'Save'}
                                        </button>
                                    </div>
                                </li>
                            ))}
                        </ul>
                    </div>
                )}
            </div>

            <div>
                <h2 className="text-xl font-semibold mb-4">Saved Addresses ({savedAddresses.length}/10)</h2>

                {savedAddresses.length === 0 ? (
                    <p className="text-gray-600">No saved addresses yet.</p>
                ) : (
                    <ul className="border rounded divide-y">
                        {savedAddresses.map((address) => (
                            <li key={address.id} className="p-4">
                                <p className="font-medium">{address.full_address}</p>
                                {address.address_data && address.address_data.data && (
                                    <p className="text-sm text-gray-600">
                                        {address.address_data.data.postal_code && `${address.address_data.data.postal_code}, `}
                                        {address.address_data.data.city || address.address_data.data.region}
                                    </p>
                                )}
                            </li>
                        ))}
                    </ul>
                )}
            </div>
        </div>
    );
};

export default Home;
