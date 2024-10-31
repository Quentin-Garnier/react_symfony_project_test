import React, { useState, useEffect } from "react";
import { useNavigate } from "react-router-dom";

const Users = () => {
    const [users, setUsers] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    
    const token = localStorage.getItem("token");
    const user_infos = localStorage.getItem("user");
    const navigate = useNavigate();

    useEffect(() => {
        // Fonction pour vérifier si l'utilisateur est un admin
        const checkAdmin = () => {
            if (user_infos) {
                const user = JSON.parse(user_infos);
                return user.is_admin === 1;
            }
            return false; // Pas de token
        };

        // Rediriger si l'utilisateur n'est pas un admin
        if (!checkAdmin()) {
            navigate('/tasks'); // Remplacez par le chemin de votre page des tâches
            return; // Ne pas continuer à charger les utilisateurs
        }

        const fetchUsers = async () => {
            try {
                const response = await fetch('http://localhost:8000/users', {
                    method: 'GET',
                    headers: {
                        Authorization: `Bearer ${token}`,
                    },
                });
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                const data = await response.json();
                setUsers(data);
                setLoading(false);
            } catch (err) {
                setError(err.message);
                setLoading(false);
            }
        };

        fetchUsers();
    }, [token, navigate, user_infos]); // Ajout de navigate au tableau de dépendances

    if (loading) {
        return <div className="text-center mt-5">Chargement...</div>;
    }

    if (error) {
        return <div className="alert alert-danger text-center mt-5">Erreur : {error}</div>;
    }

    return (
        <div className="container mt-5">
            <h1 className="text-center mb-4">Liste des utilisateurs</h1>
            <ul className="list-group">
                {users.map((user) => (
                    <li key={user.id} className="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <h5 className="mb-1">{user.name}</h5>
                            <p className="mb-1">Email: {user.email}</p>
                            <p className="mb-1">Nom: {user.nom}</p>
                            <p className="mb-1">Prénom: {user.prenom}</p>
                        </div>
                        <button
                            className="btn btn-primary"
                            onClick={() => navigate(`/responses/${user.user_id}`)}
                        >
                            Voir les réponses
                        </button>
                    </li>
                ))}
            </ul>
        </div>
    );
};

export default Users;
