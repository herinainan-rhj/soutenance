import React, { useState } from 'react';
import { useSearchParams, useNavigate, Link } from 'react-router-dom';
import api from '../../services/api'; // Ajustez le chemin vers votre instance axios si nécessaire
import './Auth.css';

export default function ResetPasswordPage() {
    const [searchParams] = useSearchParams();
    const navigate = useNavigate();
    
    // Récupération automatique du token et de l'email passés dans l'URL par l'email de l'admin
    const email = searchParams.get('email') || '';
    const token = searchParams.get('token') || '';

    const [password, setPassword] = useState('');
    const [passwordConfirmation, setPasswordConfirmation] = useState('');
    const [message, setMessage] = useState('');
    const [error, setError] = useState('');
    const [loading, setLoading] = useState(false);

    const handleReset = async (e) => {
        e.preventDefault();
        setError('');
        setMessage('');

        if (password !== passwordConfirmation) {
            setError('Les mots de passe ne correspondent pas.');
            return;
        }

        setLoading(true);

        try {
            const response = await api.post('/reset-password', {
                token,
                email,
                password,
                password_confirmation: passwordConfirmation
            });
            
            setMessage(response.data.message || 'Mot de passe créé avec succès ! Redirection vers la connexion...');
            
            // Redirection automatique vers la page de connexion après 3 secondes
            setTimeout(() => {
                navigate('/login');
            }, 3000);

        } catch (err) {
            setError(err.response?.data?.message || 'Erreur lors de la création du mot de passe. Le lien est peut-être expiré.');
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="auth-container">
            <div className="auth-card">
                {/* Logo E RAY */}
                <div className="auth-logo">
                    <img src="/eray.png" alt="Logo Eray" className="logo-circle" />
                    <span className="logo-badge-text">ERAY</span>
                </div>

                <h2>Créer votre mot de passe</h2>

                {error && <div className="auth-error">{error}</div>}
                {message && <div className="auth-success">{message}</div>}

                <form onSubmit={handleReset}>
                    <div className="form-group">
                        <label>Nouveau mot de passe</label>
                        <input
                            type="password"
                            required
                            placeholder="••••••••"
                            value={password}
                            onChange={(e) => setPassword(e.target.value)}
                        />
                    </div>

                    <div className="form-group">
                        <label>Confirmer le mot de passe</label>
                        <input
                            type="password"
                            required
                            placeholder="••••••••"
                            value={passwordConfirmation}
                            onChange={(e) => setPasswordConfirmation(e.target.value)}
                        />
                    </div>

                    <button type="submit" className="auth-btn" disabled={loading}>
                        {loading ? 'Validation en cours...' : 'Valider le mot de passe'}
                    </button>
                </form>

                <p className="auth-switch-text">
                    <Link to="/login">Retour à la connexion</Link>
                </p>
            </div>
        </div>
    );
}