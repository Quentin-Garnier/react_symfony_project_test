import React from 'react';
import 'bootstrap/dist/css/bootstrap.min.css';

const ActionButton = ({ leadId, onClick }) => {
    return (
        <button onClick={() => onClick(leadId)}>
            Action requise
        </button>
    );
};

export default ActionButton;