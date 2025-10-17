<footer class="main-footer">
        <div class="container">
            <p>&copy; <?php echo date("Y"); ?> Wazzy Pods. Todos os direitos reservados.</p>
            <p>Pods premium com design extraordinário e qualidade garantida.</p>
        </div>
    </footer>

    <script>
        document.querySelector('.menu-toggle').addEventListener('click', function() {
            document.querySelector('.nav-list').classList.toggle('active');
        });
    </script>
</body>
</html>