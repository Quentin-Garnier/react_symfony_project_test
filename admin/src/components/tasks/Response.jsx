import React from "react";
import UploadResponse from "./UploadResponse";

const Response = ({ leadId, onClick }) => {
    return (
        <UploadResponse leadId={leadId} onClick={onClick} />
    );
}

export default Response;