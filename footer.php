<footer class="main-footer">
        <div class="container">
            <p>&copy; <?php echo date("Y"); ?> Loja de Pods. Todos os direitos reservados.</p>
            <p>Este é um exemplo de site moderno e responsivo em PHP.</p>
        </div>
    </footer>

    <script>
        document.querySelector('.menu-toggle').addEventListener('click', function() {
            document.querySelector('.nav-list').classList.toggle('active');
        });
    </script>
</body>
</html>