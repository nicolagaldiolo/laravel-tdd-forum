let user = window.App.user;

// Definisco le autorizzazioni, come se fosse una policy su laravel

module.exports = {
    owns(model, prop='user_id') {
        return model[prop] === user.id;
    },

    isAdmin() {
        return ['JohnDoe', 'JaneDoe', 'NicolaGaldiolo'].includes(user.name);
    }
};