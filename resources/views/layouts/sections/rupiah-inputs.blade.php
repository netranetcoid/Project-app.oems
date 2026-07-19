{{--
  Format nominal global AppOEMS.
  Field uang ditampilkan sebagai 1.500.000, tetapi tepat sebelum form dikirim
  nilainya dikembalikan ke 1500000 agar validator Laravel dan payroll menerima
  angka murni. Field hari, bulan, persen, dan jumlah cicilan dikecualikan.
--}}
<script>
  (() => {
    const moneyName = /(^|_)(amount|salary|allowance|budget|expense|cost|price|payment|bonus|incentive|advance|principal|reimbursement|fee)(_|$)/i;
    const excludedName = /(^|_)(day|days|month|months|year|years|installment|installments|percentage|percent|rate|minutes|hours)(_|$)/i;

    const digits = (value) => {
      const text = String(value ?? '').trim();
      const decimalWithZero = text.match(/^(\d+)\.0+$/);
      return decimalWithZero ? decimalWithZero[1] : text.replace(/\D/g, '');
    };
    const format = (value) => {
      const raw = digits(value);
      return raw ? raw.replace(/\B(?=(\d{3})+(?!\d))/g, '.') : '';
    };
    const isMoneyInput = (input) => {
      if (input.dataset.rupiah === 'false' || input.type === 'hidden') return false;
      if (input.dataset.rupiah === 'true') return true;
      const name = input.name || '';
      return moneyName.test(name) && !excludedName.test(name);
    };
    const bind = (input) => {
      if (!isMoneyInput(input) || input.dataset.rupiahBound === 'true') return;
      input.dataset.rupiahBound = 'true';
      input.dataset.rupiah = 'true';
      // type=number tidak menerima pemisah ribuan. Keypad angka tetap dipakai.
      if (input.type === 'number') input.type = 'text';
      input.inputMode = 'numeric';
      input.autocomplete = 'off';
      input.value = format(input.value);
      input.addEventListener('input', () => {
        const rawBeforeCursor = input.value.slice(0, input.selectionStart || 0);
        const digitCount = digits(rawBeforeCursor).length;
        input.value = format(input.value);
        let cursor = 0;
        let seen = 0;
        while (cursor < input.value.length && seen < digitCount) {
          if (/\d/.test(input.value[cursor])) seen++;
          cursor++;
        }
        input.setSelectionRange(cursor, cursor);
      });
    };
    const bindAll = (scope = document) => scope.querySelectorAll('input[name], input[data-rupiah]').forEach(bind);
    document.addEventListener('DOMContentLoaded', () => bindAll());
    // Form mengirim angka murni: 1.500.000 menjadi 1500000.
    document.addEventListener('submit', (event) => {
      event.target.querySelectorAll('input[data-rupiah="true"]').forEach((input) => {
        input.value = digits(input.value);
      });
    }, true);
    window.OemsRupiahInput = { bindAll, format, digits };
  })();
</script>
