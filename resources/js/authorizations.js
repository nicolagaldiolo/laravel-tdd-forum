let user = window.App.user;

// Definisco le autorizzazioni, come se fosse una policy su laravel

module.exports = {
    updateReply(reply) {
        return reply.user_id === user.id;
    }
};