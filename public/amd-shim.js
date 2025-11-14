(function () {
  if (typeof define === "function") return;

  const modules = Object.create(null);

  function localRequire(name) {
    const m = modules[name];
    return m ? m.exports : undefined;
  }

  window.define = function (name, deps, factory) {
    if (typeof name !== "string") {
      factory = deps;
      deps = name;
      name = null;
    }

    deps = deps || [];

    const req = function (dep) {
      if (dep === "require") return localRequire;
      if (dep === "exports")
        return (modules[name] = modules[name] || { exports: {} }).exports;
      if (dep === "module")
        return (modules[name] = modules[name] || { exports: {} });
      return localRequire(dep);
    };

    const args = deps.map(req);

    if (name && !modules[name]) modules[name] = { exports: {} };

    const res =
      typeof factory === "function" ? factory.apply(null, args) : factory;

    if (name) {
      if (res !== undefined) modules[name].exports = res;
      else
        modules[name].exports =
          modules[name].exports || args[deps.indexOf("exports")];
    }
  };

  window.require = function (name) {
    return localRequire(name);
  };
})();
