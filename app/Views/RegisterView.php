<style>
    body {
        font-family: Arial, sans-serif;
        background: #f5f5f5;
        margin: 0;
        padding: 0;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
    }
    .auth-container {
        background: white;
        padding: 40px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        width: 100%;
        max-width: 400px;
    }
    h2 {
        text-align: center;
        color: #333;
        margin-top: 0;
    }
    form {
        display: flex;
        flex-direction: column;
    }
    input {
        padding: 12px;
        margin-bottom: 15px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
    }
    input:focus {
        outline: none;
        border-color: #cc0000;
        box-shadow: 0 0 5px rgba(204, 0, 0, 0.3);
    }
    button {
        padding: 12px;
        background: #cc0000;
        color: white;
        border: none;
        border-radius: 4px;
        font-size: 16px;
        cursor: pointer;
        font-weight: bold;
    }
    button:hover {
        background: #990000;
    }
    p {
        text-align: center;
        margin-top: 20px;
        color: #666;
    }
    p a {
        color: #cc0000;
        text-decoration: none;
    }
    p a:hover {
        text-decoration: underline;
    }
    .message {
        text-align: center;
        padding: 10px;
        margin-bottom: 15px;
        border-radius: 4px;
    }
    .error {
        color: #c33;
        background: #fee;
        border: 1px solid #fcc;
    }
    .success {
        color: #3c3;
        background: #efe;
        border: 1px solid #cfc;
    }
</style>

<div class="auth-container">
    <h2>Registrer bruker</h2>
    <form method="post" action="index.php">
        <input type="hidden" name="action" value="register">
        <input type="text" name="fname" placeholder="Fornavn" required>
        <input type="text" name="lname" placeholder="Etternavn" required>
        <input type="email" name="email" placeholder="E-post" required>
        <input type="text" name="username" placeholder="Brukernavn" required>
        <input type="password" name="password" placeholder="Passord" required>
        <button type="submit">Registrer</button>
    </form>

    <?php if (!empty($_GET['error'])): ?>
        <p class="message error"><?= htmlspecialchars($_GET['error']) ?></p>
    <?php endif; ?>
    <?php if (!empty($_GET['message'])): ?>
        <p class="message success"><?= htmlspecialchars($_GET['message']) ?></p>
    <?php endif; ?>

    <p>Allerede bruker? <a href="index.php?page=login">Logg inn her</a></p>
</div>
