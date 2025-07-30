const validation = new window.JustValidate('#form');
validation
  .addField('#full_name', [
    {
      rule: 'minLength',
      value: 6,
    },
    {
      rule: 'maxLength',
      value: 30,
    },
    {
      rule: 'required',
      errorMessage: 'Full Name is required',
    },
  ])
  .addField('#username', [
    {
      rule: 'required',
      errorMessage: 'Username is required',
    }
  ])
  .addField('#password', [
    {
      rule: 'password',
    },
    {
      rule: 'required',
      errorMessage: 'Password is required',
    }
  ]);
