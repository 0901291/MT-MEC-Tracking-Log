function onSignIn(googleUser) {
    // Useful data for your client-side scripts:
    var profile = googleUser.getBasicProfile();

    $.ajax({
        data: {
            name: profile.getName(),
            id: profile.getId(),
            email: profile.getEmail(),
            img: profile.getImageUrl()
        },
        url: "includes/login.php",
        method: "POST"
    }).success(function (output) {
        location.reload();
    })
};