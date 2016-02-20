function onSignIn(googleUser) {
    // Useful data for your client-side scripts:
    var profile = googleUser.getBasicProfile();

    $.ajax({
        data: {
            name: profile.getName(),
            id: profile.getId(),
            email: profile.getEmail(),
            img: profile.getImageUrl(),
            method: "logIn"
        },
        url: "includes/user/userCall.php",
        method: "POST"
    }).success(function (output) {
        location.reload();
    })
};