import React, { useEffect, useState } from "react";
import api from "../../services/api";
import "./AdminUsers.css";

export default function AdminUsersPage() {
    const [users, setUsers] = useState([]);
    const [searchTerm, setSearchTerm] = useState(""); // <-- État pour la recherche
    const [selectedUser, setSelectedUser] = useState(null);
    const [showModal, setShowModal] = useState(false);
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        fetchUsers();
    }, []);

    const fetchUsers = async () => {
        try {
            const response = await api.get("/admin/users");
            setUsers(response.data);
        } catch (error) {
            console.error("Erreur lors du chargement des utilisateurs", error);
        }
    };

    const handleAction = async (userId, status) => {
        setLoading(true);
        try {
            await api.post(`/admin/users/${userId}/status`, { status });
            setShowModal(false);
            setSelectedUser(null);
            fetchUsers();
        } catch (error) {
            console.error("Erreur lors de la mise à jour du statut", error);
        } finally {
            setLoading(false);
        }
    };

    const getInitials = (name) => {
        if (!name) return "U";
        return name
            .split(" ")
            .map((n) => n[0])
            .join("")
            .toUpperCase()
            .substring(0, 2);
    };

    // Filtrer la liste selon la saisie de l'utilisateur
    const filteredUsers = users.filter((user) =>
        user.name?.toLowerCase().includes(searchTerm.toLowerCase()) ||
        user.email?.toLowerCase().includes(searchTerm.toLowerCase())
    );

    return (
        <div className="admin-content-area">
            <div className="admin-header-title">
                <h2>Gestion des utilisateurs</h2>
            </div>

            {/* Barre de recherche dynamique */}
            <div className="admin-filter-bar">
                <input 
                    type="text" 
                    placeholder="Rechercher par nom ou email..." 
                    className="admin-search-input"
                    value={searchTerm}
                    onChange={(e) => setSearchTerm(e.target.value)}
                />
            </div>

            <div className="admin-table-container">
                <table className="admin-custom-table">
                    <thead>
                        <tr>
                            <th>UTILISATEUR</th>
                            <th>EMAIL</th>
                            <th>ÉTAT</th>
                            <th className="text-right">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        {filteredUsers.length > 0 ? (
                            filteredUsers.map((user) => (
                                <tr key={user.id}>
                                    <td>
                                        <div className="user-cell-info">
                                            <div className="user-avatar-badge">
                                                {getInitials(user.name)}
                                            </div>
                                            <span className="user-name-text">{user.name}</span>
                                        </div>
                                    </td>
                                    <td className="user-email-text">{user.email}</td>
                                    <td>
                                        <span className={`admin-status-pill ${user.status}`}>
                                            <span className="dot"></span>
                                            {user.status === 'valide' ? 'Validée' : user.status === 'refuse' ? 'Refusé' : 'En attente'}
                                        </span>
                                    </td>
                                    <td className="text-right">
                                        {user.status === 'en_attente' ? (
                                            <button 
                                                className="btn-respond-action"
                                                onClick={() => { setSelectedUser(user); setShowModal(true); }}
                                            >
                                                Répondre
                                            </button>
                                        ) : (
                                            <span className="text-muted-action">—</span>
                                        )}
                                    </td>
                                </tr>
                            ))
                        ) : (
                            <tr>
                                <td colSpan="4" style={{ textAlign: "center", padding: "20px", color: "#64748b" }}>
                                    Aucun utilisateur trouvé.
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>

            {/* MODALE DE RÉPONSE */}
            {showModal && selectedUser && (
                <div className="admin-modal-overlay">
                    <div className="admin-modal-card">
                        <h3>Traiter la demande</h3>
                        <p>Que souhaitez-vous faire pour le compte de <strong>{selectedUser.name}</strong> ?</p>
                        
                        <div className="admin-modal-buttons">
                            <button 
                                className="btn-modal-confirm" 
                                disabled={loading}
                                onClick={() => handleAction(selectedUser.id, 'valide')}
                            >
                                {loading ? "Traitement..." : "Confirmer"}
                            </button>
                            <button 
                                className="btn-modal-refuse" 
                                disabled={loading}
                                onClick={() => handleAction(selectedUser.id, 'refuse')}
                            >
                                {loading ? "Traitement..." : "Refuser"}
                            </button>
                            <button 
                                className="btn-modal-cancel" 
                                disabled={loading}
                                onClick={() => setShowModal(false)}
                            >
                                Annuler
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}