$.ajax({
    data: {
        name: "test",
        id: 1,
        function: "create"
    },
    url: "includes/category.php",
    method: "POST"
}).success(function (output) {
    console.log(output);
})