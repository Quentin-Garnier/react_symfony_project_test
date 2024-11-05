import React, { useState, useEffect } from "react";
import { useNavigate } from "react-router-dom";
import SwitchPermissions from "./users/SwitchPermissions";

const Users = () => {
    const [users, setUsers] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [isSuperAdmin, setIsSuperAdmin] = useState(false);

    const token = localStorage.getItem("token");
    const user_infos = localStorage.getItem("user");
    const navigate = useNavigate();

    useEffect(() => {
        const checkSuperAdmin = () => {
            if (user_infos) {
                const user = JSON.parse(user_infos);
                setIsSuperAdmin(user.super_admin === 1);
                return user.is_admin === 1;
            }
            return false;
        };

        if (!checkSuperAdmin()) {
            navigate("/tasks");
            return;
        }

        const fetchUsers = async () => {
            try {
                const response = await fetch("http://localhost:8000/users", {
                    method: "GET",
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
    }, [token, navigate, user_infos]);

    const toggleAdminStatus = async (userId, isAdmin) => {
        try {
            const response = await fetch(`http://localhost:8000/users/${userId}/toggle-admin`, {
                method: "PUT",
                headers: {
                    Authorization: `Bearer ${token}`,
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({ is_admin: !isAdmin }),
            });
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            setUsers((prevUsers) =>
                prevUsers.map((user) =>
                    user.user_id === userId ? { ...user, is_admin: !isAdmin } : user
                )
            );
        } catch (err) {
            setError(err.message);
        }
    };

    if (loading) {
        return <div className="text-center mt-5">Chargement...</div>;
    }

    if (error) {
        return <div className="alert alert-danger text-center mt-5">Erreur : {error}</div>;
    }

    return (
        <div className="container mt-5">
            <h1 className="text-center mb-4">Liste des utilisateurs</h1>
            <table className="table table-striped">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Admin</th>
                        {isSuperAdmin && <th>Rôle</th>}
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    {users.map((user) => (
                        <tr key={user.user_id}>
                            <td>{user.nom}</td>
                            <td>{user.email}</td>
                            <td>{user.nom}</td>
                            <td>{user.prenom}</td>
                            <td>{user.is_admin ? "Oui" : "Non"}</td>
                            {isSuperAdmin && (
                                <td>
                                    <SwitchPermissions
                                        userId={user.user_id}
                                        isAdmin={user.is_admin}
                                        isSuperAdmin={user.super_admin}
                                        onToggle={toggleAdminStatus}
                                    />
                                </td>
                            )}
                            <td>
                                <button
                                    className="btn btn-primary ml-2"
                                    onClick={() => navigate(`/responses/${user.user_id}`)}
                                >
                                    Voir les réponses
                                </button>
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
};

export default Users;
