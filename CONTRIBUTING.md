# Contributing to DarwinNatha Process

First of all, thank you for taking the time to contribute to **DarwinNatha Process**!  
Open source thrives because of your help and ideas.

Please follow the guidelines below to help us keep this project clean, consistent, and maintainable.

---

## 🛠️ Getting Started

1. **Fork** the repository.
2. **Clone** your fork locally:
```bash
   git clone https://github.com/darwinnatha/laravel-process.git
   cd laravel-process
```

3. Install dependencies:

   ```bash
   composer install
   ```

4. Run tests to ensure everything works:

   ```bash
   vendor/bin/pest
   ```

---

## 🚀 Making Changes

### ✏️ Code Style

This project follows **PSR-12** and Laravel's style conventions.
Use [Laravel Pint](https://laravel.com/docs/pint) to format your code:

```bash
vendor/bin/pint
```

### ✅ Tests

All new features or bug fixes **must be covered by tests**.
Use PestPHP + Mockery + Orchestra Testbench for writing your tests.

Run them with:

```bash
vendor/bin/pest
```

---

## 📄 Creating a Pull Request

1. **Create a new branch**:

   ```bash
   git checkout -b feature/your-feature-name
   ```

2. **Commit your changes** with clear messages:

   ```bash
   git commit -m "[feature] Adds stub generation for custom groups"
   ```

3. **Push your branch** to your fork:

   ```bash
   git push origin feature/your-feature-name
   ```

4. Open a Pull Request against the `main` branch.

---

## 💡 Tips for Great Contributions

* Keep Pull Requests **focused and atomic**.
* Don’t hesitate to **open an issue** before starting work, to align on the feature or fix.
* If you're fixing a bug, **include the failing test first**, then your fix.

---

## 🙏 Thank You

Your input makes this package better. Whether it's:

* Reporting a bug 🐞
* Suggesting an improvement 💡
* Submitting a PR ⚙️
* Or just sharing the package 💬

You're part of the project. Thanks again! ❤️

