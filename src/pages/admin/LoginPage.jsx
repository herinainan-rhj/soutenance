import React, { useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import api from '../../services/api'; // Ajustez le chemin selon votre projet
import './Auth.css';

export default function LoginPage() {
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [error, setError] = useState('');
    const [loading, setLoading] = useState(false);
    const navigate = useNavigate();

    const handleLogin = async (e) => {
        e.preventDefault();
        setError('');
        setLoading(true);

        try {
            const response = await api.post('/login', { email, password });
            
            // Stockage des informations d'authentification
            localStorage.setItem('token', response.data.token);
            localStorage.setItem('user', JSON.stringify(response.data.user));

            // Redirection vers le dashboard admin ou le portail public
            navigate('/admin/users');
        } catch (err) {
            setError(err.response?.data?.message || 'Identifiants incorrects ou compte non validé.');
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

                <h2>Connexion</h2>

                {error && <div className="auth-error">{error}</div>}

                <form onSubmit={handleLogin}>
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

                    <div className="form-group">
                        <label>Mot de passe</label>
                        <input
                            type="password"
                            required
                            placeholder="••••••••"
                            value={password}
                            onChange={(e) => setPassword(e.target.value)}
                        />
                    </div>

                    <div className="auth-links-row">
                        <Link to="/forgot-password" className="auth-link">
                            Mot de passe oublié ?
                        </Link>
                    </div>

                    <button type="submit" className="auth-btn" disabled={loading}>
                        {loading ? 'Connexion en cours...' : 'Se connecter'}
                    </button>
                </form>

                <p className="auth-switch-text">
                    Pas encore de compte ? <Link to="/register">S'inscrire</Link>
                </p>
            </div>
        </div>
    );
}