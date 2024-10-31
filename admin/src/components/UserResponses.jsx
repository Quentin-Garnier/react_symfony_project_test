import React, { useEffect, useState } from "react";
import { useParams, useNavigate } from "react-router-dom";
import "bootstrap/dist/css/bootstrap.min.css";

const UserResponses = () => {
    const { id } = useParams();
    const [responses, setResponses] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    
    const token = localStorage.getItem("token");
    const user_infos = localStorage.getItem("user");
    const navigate = useNavigate(); // Ajout de useNavigate

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
            return; // Ne pas continuer à charger les réponses
        }

        const fetchResponses = async () => {
            try {
                const response = await fetch(`http://localhost:8000/responses/${id}`, {
                    method: 'GET',
                    headers: {
                        Authorization: `Bearer ${token}`,
                    },
                });
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                const data = await response.json();
                setResponses(data);
                setLoading(false);
            } catch (err) {
                setError(err.message);
                setLoading(false);
            }
        };

        fetchResponses();
    }, [id, token, navigate, user_infos]); // Ajout de navigate au tableau de dépendances

    const downloadTaskCSV = (response) => {
        const headers = ['Nom', 'Description'];
        const rows = [[response.nom, response.description]];

        const csvContent = [
            headers.join(','), 
            ...rows.map(row => row.join(',')) 
        ].join('\n');

        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);

        const link = document.createElement("a");
        link.href = url;
        link.setAttribute("download", `task_${response.nom}_response.csv`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    };

    if (loading) {
        return <div className="text-center mt-5">Chargement...</div>;
    }

    if (error) {
        return <div className="alert alert-danger text-center mt-5">Erreur : {error}</div>;
    }

    return (
        <div className="container mt-5">
            <h1 className="text-center mb-4">Réponses de l'utilisateur {id}</h1>
            {responses.length === 0 ? (
                <p className="text-center">Cet utilisateur n'a encore répondu à aucune tâche.</p>
            ) : (
                <table className="table table-bordered">
                    <thead className="thead-light">
                        <tr>
                            <th>Nom</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        {responses.map((response) => (
                            <tr key={response.id}>
                                <td>{response.nom}</td>
                                <td>
                                    <button
                                        className="btn btn-primary"
                                        onClick={() => downloadTaskCSV(response)}
                                    >
                                        Télécharger en CSV
                                    </button>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            )}
        </div>
    );
};

export default UserResponses;
