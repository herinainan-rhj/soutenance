import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import api from '../../services/api'; // Ajustez le chemin selon votre projet
import './Auth.css';

export default function RegisterPage() {
    const [name, setName] = useState('');
    const [email, setEmail] = useState('');
    const [success, setSuccess] = useState('');
    const [error, setError] = useState('');
    const [loading, setLoading] = useState(false);

    const handleRegister = async (e) => {
        e.preventDefault();
        setError('');
        setSuccess('');
        setLoading(true);

        try {
            const response = await api.post('/register', { name, email });
            setSuccess(response.data.message || 'Inscription réussie ! Votre compte est en attente de validation par un administrateur.');
            setName('');
            setEmail('');
        } catch (err) {
            setError(err.response?.data?.message || "Erreur lors de l'inscription.");
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="auth-container">
            <div className="auth-card">
                {/* Logo E RAY conforme au header public */}
                <div className="auth-logo">
                    <img src="/eray.png" alt="Logo Eray" className="logo-circle" />
                    <span className="logo-badge-text">ERAY</span>
                </div>

                <h2>Inscription</h2>

                {error && <div className="auth-error">{error}</div>}
                {success && <div className="auth-success">{success}</div>}

                <form onSubmit={handleRegister}>
                    <div className="form-group">
                        <label>Nom complet</label>
                        <input
                            type="text"
                            required
                            placeholder="Votre nom"
                            value={name}
                            onChange={(e) => setName(e.target.value)}
                        />
                    </div>

                    <div className="form-group">
                        <label>Adresse e-mail</label>
                        <input
                            type="email"
                            required
                            placeholder="votre@email.com"
                            value={email}
                            onChange={(e) => setEmail(e.target.value)}
                        />
                    </div>

                    <button type="submit" className="auth-btn" disabled={loading}>
                        {loading ? 'S\'inscription...' : 'S\'inscrire'}
                    </button>
                </form>

                <p className="auth-switch-text">
                    Déjà un compte ? <Link to="/login">Se connecter</Link>
                </p>
            </div>
        </div>
    );
}