<form action="/login" method="post" style="max-width: 360px; margin: 40px auto; padding: 24px; border: 1px solid #e5e7eb; border-radius: 6px; background: #fff;">
    <h3 style="margin: 0 0 16px; font-size: 1.2rem; color: #0f172a;">Login</h3>
    <div style="margin-bottom: 12px;">
        <label for="username" style="display: block; font-size: 0.85rem; margin-bottom: 4px; font-weight: 500;">Username</label>
        <input type="text" name="username" id="username" placeholder="Username" style="width: 100%; padding: 8px; border: 1px solid #e5e7eb; border-radius: 4px; box-sizing: border-box;">
    </div>
    <div style="margin-bottom: 16px;">
        <label for="password" style="display: block; font-size: 0.85rem; margin-bottom: 4px; font-weight: 500;">Password</label>
        <input type="password" name="password" id="password" placeholder="Password" style="width: 100%; padding: 8px; border: 1px solid #e5e7eb; border-radius: 4px; box-sizing: border-box;">
    </div>
    <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '') ?>">
    <button type="submit" style="width: 100%; background: #0f172a; color: #fff; border: none; padding: 8px; border-radius: 4px; font-weight: 500; cursor: pointer;">Login</button>
</form>