<script type="text/javascript">
    function closethisasap() {
        document.forms["redirectpost"].submit();
    }
</script>
</head>
<body onload="closethisasap();">
    <form name="redirectpost" method="POST" action="{{ Config::get('jazzcashCheckout.jazzcash.CARD_REFUND_POST_URL') }}">
        @foreach ($post_data as $key => $value)
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endforeach

    </form>
</body>

</html>
